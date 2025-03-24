<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntreeRequest;
use App\Http\Requests\UpdateEntreeRequest;
use App\Models\Commande;
use App\Models\Entree;
use Inertia\Inertia;

class StockageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Entree/Index', ['items'=>Commande::paginate(10)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Entree/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEntreeRequest $request)
    {
        //return $request->all();
        $commande = new Commande();
        $commande->n_facture = $request['n_facture'];
        $commande->fournisseur_id = $request['fournisseur_id'];
        $commande->n_bon = $request['n_bon'];
        $commande->date = $request['date'];
        $commande->paiement =  $request['paiement'];
        $commande->dateEcheance =  isset($request['dateEcheance'])?$request['dateEcheance']:null;
        $commande->save();
        foreach ($request->produits as $produit) {
            $data = array_merge($produit['entree'], $request->all());
            $data['commande_id'] = $commande->id;
            $entree = Entree::create($data);
            if (isset($produit['tva'])){
                $entree->tva = $produit['tva'];
            }
            $commande->montantPaye += $entree->qte * $entree->prix_achat;
           // unset($produit['entree']['stock']);
            $commande->produits()->attach($produit['id']);
            $commande->produits()->updateExistingPivot($produit['id'], $produit['entree']);
            $entree->update();
        }
        $commande->update();
        return ['message' => 'Entrées enregistrée'];

    }

    /**
     * Display the specified resource.
     */
    public function show(Entree $entree)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Entree $entree)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEntreeRequest $request, Entree $entree)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Entree $entree)
    {
        //
    }
}
