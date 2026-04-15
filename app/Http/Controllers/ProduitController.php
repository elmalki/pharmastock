<?php

namespace App\Http\Controllers;

use App\Exports\ProduitsExport;
use App\Exports\ProduitsPerimesExport;
use App\Http\Requests\StoreProduitRequest;
use App\Http\Requests\UpdateProduitRequest;
use App\Models\Categorie;
use App\Models\Commande;
use App\Models\CommandeProduit;
use App\Models\Destockage;
use App\Models\Produit;
use App\Models\User;
use App\Notifications\DashboardNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
     //   Gate::authorize('viewAny', Produit::class);
        $query = Produit::withSum('lots as lots_sum_qte', 'qte');

        // Filters
        if (request()->filled('categorie_id')) {
            $query->where('categorie_id', request()->categorie_id);
        }
        if (request()->filled('stock_status')) {
            $status = request()->stock_status;
            if ($status === 'rupture') {
                $query->having('lots_sum_qte', '=', 0)->orHavingNull('lots_sum_qte');
            } elseif ($status === 'critique') {
                $query->havingRaw('lots_sum_qte > 0 AND lots_sum_qte <= limit_command');
            } elseif ($status === 'disponible') {
                $query->havingRaw('lots_sum_qte > limit_command');
            }
        }

        // Stats (computed across all products, not filtered)
        $allProducts = Produit::withSum('lots as lots_sum_qte', 'qte')->get();
        $totalProducts = $allProducts->count();
        $rupture = $allProducts->filter(fn($p) => ($p->lots_sum_qte ?? 0) == 0)->count();
        $critique = $allProducts->filter(fn($p) => ($p->lots_sum_qte ?? 0) > 0 && ($p->lots_sum_qte ?? 0) <= ($p->limit_command ?? 0))->count();
        $disponible = $totalProducts - $rupture - $critique;
        $totalValue = $allProducts->sum(fn($p) => ($p->lots_sum_qte ?? 0) * ($p->prix_public ?? 0));

        return Inertia::render('Produits/Index', [
            'items' => $query
                ->orderBy(request()->field ?? 'id', request()->order == 1 ? 'desc' : 'asc')
                ->paginate(10)
                ->withQueryString(),
            'sort_fields' => request(),
            'categories' => Categorie::all(),
            'stats' => [
                'total' => $totalProducts,
                'rupture' => $rupture,
                'critique' => $critique,
                'disponible' => $disponible,
                'total_value' => $totalValue,
            ],
            'applied_filters' => [
                'categorie_id' => request()->categorie_id,
                'stock_status' => request()->stock_status,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Produits/Create',['categories'=>Categorie::all()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProduitRequest $request)
    {
        Produit::create($request->all());
        return redirect()->route('produits.index')->banner('Produit crée avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(Produit $produit)
    {
        return  Inertia::render('Produits/Show',['produit'=>$produit->load('commandes'),'categories'=>Categorie::all()]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produit $produit)
    {
        return Inertia::render('Produits/Edit',['item'=>$produit,'categories'=>Categorie::all()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProduitRequest $request, Produit $produit)
    {
        $produit->update($request->all());
        if ($request->header('X-From-Show')) {
            return redirect()->route('produits.show', $produit)->banner('Produit modifié');
        }
        return redirect()->route('produits.index')->banner('Produit modifié');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produit $produit)
    {
        $produit->delete();
        return redirect()->route('produits.index')->dangerBanner('produit supprimé');
    }
    public function export()
    {
        return Excel::download(new ProduitsExport(), 'stock'.date('_d_m_y_').'.xlsx');
    }
    public function exportPerimes()
    {
        return Excel::download(new ProduitsPerimesExport(), 'stock_perime'.date('_d_m_y_').'.xlsx');
    }


    public function stock()
    {
        $produits = Produit::with('categorie')
            ->withSum('lots as lots_sum_qte', 'qte')
            ->withSum(['lots as lots_perime_sum' => fn($q) => $q->where('expirationDate', '<', now())], 'qte')
            ->orderBy('label')
            ->get();

        $stats = [
            'total' => $produits->count(),
            'rupture' => $produits->filter(fn($p) => ($p->qte ?? 0) == 0)->count(),
            'critique' => $produits->filter(fn($p) => ($p->qte ?? 0) > 0 && ($p->qte ?? 0) <= ($p->limit_command ?? 0))->count(),
            'valeur' => $produits->sum(fn($p) => ($p->qte ?? 0) * ($p->prix_public ?? 0)),
        ];

        return Pdf::loadView('pdf.stock', compact('produits', 'stats'))
            ->setPaper('A4', 'landscape')
            ->stream();
    }
    public function perimesPDF()
    {

        return Pdf::loadView(
            'pdf.perimes',
            ['items'=>CommandeProduit::whereNotNull('expirationDate')->where('expirationDate','<',now())->with('produit')->get()]
        )
            // ->setPaper('A4', 'landscape')
            ->stream();
    }


    public function barcodes()
    {
        $pdf = Pdf::loadView(
            'pdf.barcodes',
            ['produits'=>Produit::where('generated',1)->get()]
        )
            ->stream();
        return $pdf;
    }
    public function perimes()
    {
        return Inertia::render('Produits/Perimes', ['items'=>CommandeProduit::whereNotNull('expirationDate')->where('expirationDate','<',now())->where('qte','>',0)->with('produit.categorie')->get()]);
    }
}
