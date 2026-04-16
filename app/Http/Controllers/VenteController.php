<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVenteRequest;
use App\Http\Requests\UpdateVenteRequest;
use App\Models\CommandeProduit;
use App\Models\Vente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Vente::with('produits', 'client');

        // Filters
        if (request()->filled('statut')) {
            $query->where('statut', request()->statut);
        }
        if (request()->filled('paiement')) {
            $query->where('paiement', request()->paiement);
        }
        if (request()->filled('date_from')) {
            $query->whereDate('date', '>=', request()->date_from);
        }
        if (request()->filled('date_to')) {
            $query->whereDate('date', '<=', request()->date_to);
        }

        // Stats (computed across all ventes, not filtered)
        $allVentes = Vente::all();
        $totalCA = $allVentes->sum(fn($v) => $v->total);
        $totalBenefice = (float) DB::table('vente_produit')
            ->join('ventes', 'vente_produit.vente_id', '=', 'ventes.id')
            ->whereNull('ventes.deleted_at')
            ->selectRaw('COALESCE(SUM((vente_produit.prix - vente_produit.prix_achat) * vente_produit.qte), 0) as v')
            ->value('v');
        $totalImpayes = $allVentes->where('statut', 'partiel')->count();
        $totalResteImpayes = $allVentes->sum('reste');

        return Inertia::render('Ventes/Index', [
            'items' => $query
                ->orderBy(request()->field ?? 'id', (int) request('order', -1) === 1 ? 'asc' : 'desc')
                ->paginate(10)
                ->withQueryString(),
            'sort_fields' => request(),
            'stats' => [
                'total' => $allVentes->count(),
                'ca' => $totalCA,
                'benefice' => $totalBenefice,
                'impayes' => $totalImpayes,
                'reste_impayes' => $totalResteImpayes,
                'payes' => $allVentes->where('statut', 'payé')->count(),
            ],
            'applied_filters' => [
                'statut' => request()->statut,
                'paiement' => request()->paiement,
                'date_from' => request()->date_from,
                'date_to' => request()->date_to,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Ventes/Create', [
            'nextNumero' => self::nextNumeroFacture(),
        ]);
    }

    private static function nextNumeroFacture(): string
    {
        $year = now()->year;
        $last = Vente::withTrashed()
            ->whereYear('created_at', $year)
            ->where('n_facture', 'like', "%/$year")
            ->orderByRaw("CAST(SUBSTRING_INDEX(n_facture, '/', 1) AS UNSIGNED) DESC")
            ->value('n_facture');

        $next = $last ? (int) explode('/', $last)[0] + 1 : 1;

        return "$next/$year";
    }

    /**
     * Handle a returned product line (negative sortie): decrement the qte
     * on the latest matching vente_produit row and restock the lot.
     */
    private function applyReturn(array $produit, array $lotData, CommandeProduit $lot, ?int $clientId, ?int $excludeVenteId = null): void
    {
        $returnedQty = abs($lotData['sortie']);

        $query = DB::table('vente_produit as vp')
            ->join('ventes as v', 'v.id', '=', 'vp.vente_id')
            ->where('vp.produit_id', $produit['id'])
            ->where('vp.qte', '>=', $returnedQty);

        if ($clientId !== null) {
            $query->where('v.client_id', $clientId);
        } else {
            $query->whereNull('v.client_id');
        }

        if ($excludeVenteId !== null) {
            $query->where('v.id', '!=', $excludeVenteId);
        }

        $latest = $query->orderBy('v.id', 'desc')
            ->select('vp.vente_id', 'vp.produit_id', 'vp.lot_id')
            ->first();

        if (!$latest) {
            throw new \Exception("Aucune vente précédente trouvée contenant au moins {$returnedQty} unités de {$produit['label']} pour ce client.");
        }

        DB::table('vente_produit')
            ->where('vente_id', $latest->vente_id)
            ->where('produit_id', $latest->produit_id)
            ->where('lot_id', $latest->lot_id)
            ->decrement('qte', $returnedQty);

        $lot->increment('qte', $returnedQty);
    }

    /**
     * Sync a caisse mouvement for this vente if paiement = Éspèce.
     * Positive montantPaye → entrée; negative → sortie (refund).
     * Deletes any existing mouvement tied to this vente first (for updates).
     */
    private function syncCaisseMouvement(Vente $vente): void
    {
        \App\Models\CaisseMouvement::where('vente_id', $vente->id)->delete();

        $paiement = $vente->paiement?->value ?? $vente->paiement;
        if ($paiement !== \App\Enums\ModePaiement::ESPECE->value) return;

        $montant = (float) $vente->montantPaye;
        if ($montant == 0) return;

        \App\Models\CaisseMouvement::create([
            'type'    => $montant > 0 ? 'entree' : 'sortie',
            'montant' => abs($montant),
            'motif'   => $montant > 0
                ? "Vente #{$vente->n_facture}"
                : "Remboursement vente #{$vente->n_facture}",
            'vente_id' => $vente->id,
            'user_id'  => Auth::id(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVenteRequest $request)
    {
        DB::beginTransaction();

        try {
            // 1. Calculer le sous-total
            $subtotal = 0;
            foreach ($request->produits as $produit) {
                foreach ($produit['lots'] as $lot) {
                    $subtotal += $lot['sortie'] * $lot['prix_vente'];
                }
            }

            // 2. Calculer la remise et le total
            $remisePercent = (float)$request->remise;
            $remiseAmount = ($subtotal * $remisePercent) / 100;
            $total = $subtotal - $remiseAmount;

            // 3. Créer la vente principale
            $vente = Vente::create([
                'n_facture' => $request->n_facture,
                'client_id' => $request->client_id,
                'user_id' => Auth::id(),
                'date' => $request->date,
                'paiement' => $request->paiement,
                'dateEcheance' => $request->dateEcheance,
                'subtotal' => $subtotal,
                'remise' => $remisePercent,
                'remise_amount' => $remiseAmount,
                'montantPaye' => $request->montantPaye,
                'reste' => $total - $request->montantPaye,
                'statut' => $request->montantPaye >= $total ? 'payé' : 'partiel',
            ]);

            // 4. Traiter chaque produit et ses lots
            foreach ($request->produits as $produit) {
                foreach ($produit['lots'] as $lotData) {
                    if ($lotData['sortie'] == 0) continue;

                    // Vérifier que le lot existe et a assez de stock
                    $lot = CommandeProduit::where('n_lot', $lotData['n_lot'])
                        ->where('produit_id', $produit['id'])
                        ->where('commande_id', $lotData['commande_id'])
                        ->first();

                    if (!$lot) {
                        throw new \Exception("Lot {$lotData['n_lot']} introuvable pour le produit {$produit['label']}");
                    }

                    // Retour d'un médicament (avoir)
                    if ($lotData['sortie'] < 0) {
                        $this->applyReturn($produit, $lotData, $lot, $request->client_id);
                        continue;
                    }

                    if ($lot->qte < $lotData['sortie']) {
                        throw new \Exception("Stock insuffisant pour le lot {$lotData['n_lot']}. Disponible: {$lot->qte}, Demandé: {$lotData['sortie']}");
                    }

                    $vente->produits()->attach($produit['id'], [
                        'lot_id' => $lot->id,
                        'remise' => $produit['remise'] ?? 0,
                        'qte' => $lotData['sortie'],
                        'prix' => $lotData['prix_vente'],
                        'prix_achat' => $lot->prix_achat ?? 0,
                        'tva' => $lotData['tva'] ?? 0,
                    ]);

                    // Décrémenter le stock du lot
                    $lot->decrement('qte', $lotData['sortie']);
                }
            }

            $this->syncCaisseMouvement($vente);
            DB::commit();

            return redirect()->route('ventes.index')
                ->with('success', "Vente #{$vente->n_facture} créée avec succès! Total: " . number_format($total, 2) . " MAD");


        }catch
        (\Exception $e) {
            // Rollback en cas d'erreur
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Erreur lors de la création de la vente: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function pdf(Vente $vente)
    {
        $vente->load('produits', 'client', 'user');
        return view('pdf.facture', [
            'vente' => $vente]);
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.facture', [
            'vente' => $vente,
        ])->stream("Facture-{$vente->n_facture}.pdf");
    }

    /**
     * Display the specified resource.
     */
    public function show(Vente $vente)
    {
        return Inertia::render('Ventes/Show', [
            'vente' => $vente->load('produits', 'client', 'user'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vente $vente)
    {
        $vente->load('produits', 'client');

        // Group sold items by product
        $soldByProduct = [];
        foreach ($vente->produits as $produit) {
            $pid = $produit->id;
            if (!isset($soldByProduct[$pid])) {
                $soldByProduct[$pid] = [
                    'id' => $produit->id,
                    'label' => $produit->label,
                    'barcode' => $produit->barcode,
                    'sold_lots' => [],
                ];
            }
            $soldByProduct[$pid]['sold_lots'][$produit->pivot->lot_id] = $produit->pivot->qte;
        }

        // For each product, load available lots (with stock or part of this sale)
        $venteProduits = [];
        foreach ($soldByProduct as $data) {
            $soldLots = $data['sold_lots'];
            $lotIds = array_keys($soldLots);

            $lots = CommandeProduit::where('produit_id', $data['id'])
                ->where(function ($q) use ($lotIds) {
                    $q->where('qte', '>', 0)->orWhereIn('id', $lotIds);
                })
                ->get()
                ->map(function ($lot) use ($soldLots) {
                    $soldQte = $soldLots[$lot->id] ?? 0;
                    return [
                        'id' => $lot->id,
                        'n_lot' => $lot->n_lot,
                        'commande_id' => $lot->commande_id,
                        'produit_id' => $lot->produit_id,
                        'prix_vente' => $lot->prix_vente,
                        'prix_achat' => $lot->prix_achat,
                        'qte' => $lot->qte + $soldQte,
                        'expirationDate' => $lot->expirationDate,
                        'created_at' => $lot->created_at,
                        'sortie' => $soldQte,
                    ];
                })
                ->values()
                ->toArray();

            $venteProduits[] = [
                'id' => $data['id'],
                'label' => $data['label'],
                'barcode' => $data['barcode'],
                'lots' => $lots,
            ];
        }

        return Inertia::render('Ventes/Edit', [
            'vente' => $vente,
            'venteProduits' => $venteProduits,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVenteRequest $request, Vente $vente)
    {
        DB::beginTransaction();

        try {
            // 1. Restore stock from old sale
            foreach ($vente->produits as $produit) {
                $lot = CommandeProduit::find($produit->pivot->lot_id);
                if ($lot) {
                    $lot->increment('qte', $produit->pivot->qte);
                }
            }

            // 2. Detach old products
            $vente->produits()->detach();

            // 3. Calculate new totals
            $subtotal = 0;
            foreach ($request->produits as $produit) {
                foreach ($produit['lots'] as $lot) {
                    if ($lot['sortie'] == 0) continue;
                    $subtotal += $lot['sortie'] * $lot['prix_vente'];
                }
            }

            $remisePercent = (float) $request->remise;
            $remiseAmount = ($subtotal * $remisePercent) / 100;
            $total = $subtotal - $remiseAmount;

            // 4. Update the vente
            $vente->update([
                'n_facture' => $request->n_facture,
                'client_id' => $request->client_id,
                'date' => $request->date,
                'paiement' => $request->paiement,
                'dateEcheance' => $request->dateEcheance,
                'subtotal' => $subtotal,
                'remise' => $remisePercent,
                'remise_amount' => $remiseAmount,
                'montantPaye' => $request->montantPaye,
                'reste' => $total - $request->montantPaye,
                'statut' => $request->montantPaye >= $total ? 'payé' : 'partiel',
            ]);

            // 5. Attach new products and decrement stock
            foreach ($request->produits as $produit) {
                foreach ($produit['lots'] as $lotData) {
                    if ($lotData['sortie'] == 0) continue;

                    $lot = CommandeProduit::where('n_lot', $lotData['n_lot'])
                        ->where('produit_id', $produit['id'])
                        ->where('commande_id', $lotData['commande_id'])
                        ->first();

                    if (!$lot) {
                        throw new \Exception("Lot {$lotData['n_lot']} introuvable pour le produit {$produit['label']}");
                    }

                    // Retour d'un médicament (avoir)
                    if ($lotData['sortie'] < 0) {
                        $this->applyReturn($produit, $lotData, $lot, $request->client_id, $vente->id);
                        continue;
                    }

                    if ($lot->qte < $lotData['sortie']) {
                        throw new \Exception("Stock insuffisant pour le lot {$lotData['n_lot']}. Disponible: {$lot->qte}, Demandé: {$lotData['sortie']}");
                    }

                    $vente->produits()->attach($produit['id'], [
                        'lot_id' => $lot->id,
                        'remise' => $produit['remise'] ?? 0,
                        'qte' => $lotData['sortie'],
                        'prix' => $lotData['prix_vente'],
                        'prix_achat' => $lot->prix_achat ?? 0,
                        'tva' => $produit['tva'] ?? 0,
                    ]);

                    $lot->decrement('qte', $lotData['sortie']);
                }
            }

            $this->syncCaisseMouvement($vente);
            DB::commit();

            return redirect()->route('ventes.index')
                ->banner("Vente #{$vente->n_facture} modifiée avec succès!");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Erreur lors de la modification: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vente $vente)
    {
        DB::beginTransaction();

        try {
            // Restore stock from each sold lot
            foreach ($vente->produits as $produit) {
                $lot = CommandeProduit::find($produit->pivot->lot_id);
                if ($lot) {
                    $lot->increment('qte', $produit->pivot->qte);
                }
            }

            // Detach products from pivot table
            $vente->produits()->detach();

            // Remove caisse mouvement linked to this vente (cash payment)
            \App\Models\CaisseMouvement::where('vente_id', $vente->id)->delete();

            // Soft delete the vente
            $vente->delete();

            DB::commit();

            return redirect()->route('ventes.index')
                ->dangerBanner('Vente supprimée');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }
}
