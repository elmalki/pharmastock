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
        Gate::authorize('viewAny', Produit::class);
        return Inertia::render('Produits/Index', ['items'=>Produit::orderBy(request()->field??'id', request()->order==1?'desc':'asc')->paginate(10),'sort_fields'=>request()]);
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
        return  Inertia::render('Produits/Show',['produit'=>$produit->load('commandes')]);
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
        $pdf = Pdf::loadView(
            'pdf.stock',
            ['produits'=>Produit::all()]
        )
            // ->setPaper('A4', 'landscape')
            ->stream();
        return $pdf;
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
        return Inertia::render('Produits/Perimes', ['items'=>CommandeProduit::whereNotNull('expirationDate')->where('expirationDate','<',now())->with('produit')->get()]);
    }
}
