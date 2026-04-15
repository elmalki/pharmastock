<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFournisseurRequest;
use App\Http\Requests\UpdateFournisseurRequest;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FournisseurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Fournisseurs/Index', ['items'=>Fournisseur::withCount('commandes')->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Fournisseurs/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFournisseurRequest $request)
    {
        Fournisseur::create($request->all());
        return to_route('fournisseurs.index')->banner('Fournisseur ajouté');
    }

    /**
     * Display the specified resource.
     */
    public function show(Fournisseur $fournisseur, Request $request)
    {
        $totalsSub = '(SELECT commande_id, SUM(prix_achat * qte_achete) as total_ht FROM commande_produit GROUP BY commande_id)';

        $sortField = in_array($request->input('field'), ['n_bon', 'n_facture', 'date', 'dateEcheance', 'situation', 'paiement', 'montantPaye', 'total_ht', 'reste'])
            ? $request->input('field')
            : 'date';
        $sortOrder = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $perPage = max(5, min(100, (int) $request->input('per_page', 10)));

        $orderClause = match ($sortField) {
            'total_ht' => 'totals.total_ht',
            'reste'    => 'reste',
            default    => "commandes.$sortField",
        };

        $commandes = DB::table('commandes')
            ->leftJoin(DB::raw("$totalsSub as totals"), 'totals.commande_id', '=', 'commandes.id')
            ->where('commandes.fournisseur_id', $fournisseur->id)
            ->whereNull('commandes.deleted_at')
            ->select(
                'commandes.id',
                'commandes.n_bon',
                'commandes.n_facture',
                'commandes.date',
                'commandes.dateEcheance',
                'commandes.paiement',
                'commandes.situation',
                'commandes.montantPaye',
                DB::raw('COALESCE(totals.total_ht, 0) as total_ht'),
                DB::raw('GREATEST(COALESCE(totals.total_ht, 0) - COALESCE(commandes.montantPaye, 0), 0) as reste')
            )
            ->orderBy(DB::raw($orderClause), $sortOrder)
            ->paginate($perPage)
            ->withQueryString();

        $kpiRow = DB::table('commandes')
            ->leftJoin(DB::raw("$totalsSub as totals"), 'totals.commande_id', '=', 'commandes.id')
            ->where('commandes.fournisseur_id', $fournisseur->id)
            ->whereNull('commandes.deleted_at')
            ->selectRaw('
                COUNT(*) as total_commandes,
                COALESCE(SUM(totals.total_ht), 0) as total_montant,
                COALESCE(SUM(commandes.montantPaye), 0) as total_paye,
                COALESCE(SUM(GREATEST(COALESCE(totals.total_ht, 0) - COALESCE(commandes.montantPaye, 0), 0)), 0) as total_reste,
                MAX(commandes.date) as last_commande_date
            ')
            ->first();

        $total = (int) $kpiRow->total_commandes;
        $kpis = [
            'total_commandes' => $total,
            'total_montant'   => (float) $kpiRow->total_montant,
            'total_paye'      => (float) $kpiRow->total_paye,
            'total_reste'     => (float) $kpiRow->total_reste,
            'panier_moyen'    => $total > 0 ? (float) $kpiRow->total_montant / $total : 0,
            'last_commande'   => $kpiRow->last_commande_date,
        ];

        return Inertia::render('Fournisseurs/Show', [
            'fournisseur' => $fournisseur,
            'kpis'        => $kpis,
            'commandes'   => $commandes,
            'sort'        => ['field' => $sortField, 'order' => $sortOrder, 'per_page' => $perPage],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fournisseur $fournisseur)
    {
        return Inertia::render('Fournisseurs/Edit', ['item'=>$fournisseur]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFournisseurRequest $request, Fournisseur $fournisseur)
    {
        $fournisseur->update($request->all());
        return redirect()->route('fournisseurs.index')->banner('Fournisseur modifié');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fournisseur $fournisseur)
    {
        $fournisseur->delete();
        return to_route('fournisseurs.index')->dangerBanner('Fournisseur suprimé');
    }
}
