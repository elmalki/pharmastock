<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommandeRequest;
use App\Http\Requests\UpdateCommandeRequest;
use App\Models\Commande;
use Carbon\Carbon;
use Inertia\Inertia;

class CommandeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Commandes/Index', ['items'=>Commande::orderBy(request()->field??'id', request()->order==1?'desc':'asc')->paginate(10),'sort_fields'=>request()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Commandes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommandeRequest $request)
    {
       // return $request->all();
        $commande = new Commande();
        $commande->n_facture = $request['n_facture'];
        $commande->fournisseur_id = $request['fournisseur_id'];
        $commande->n_bon = $request['n_bon'];
        $commande->date = $request['date'];
        $commande->situation = $request['situation'];
        $commande->paiement =  $request['paiement'];
        $commande->dateEcheance =  isset($request['dateEcheance'])?$request['dateEcheance']:null;
        $commande->save();
        foreach ($request->produits as $produit) {
            $data = $produit['entree'];
            $data['expirationDate'] =substr($data['expirationDate'],0,10);
            $data['qte_achete'] = $produit['entree']['qte'];
            $commande->montantPaye += $data['qte_achete'] * $data['prix_achat'];
            unset($produit['entree']['created_at']);
            $commande->produits()->attach($produit['id'],$data);
        }
        $commande->update();
        return redirect()->route('commandes.index')->banner('Commande crée avec succes');

    }

    /**
     * Display the specified resource.
     */
    public function show(Commande $commande)
    {
        return Inertia::render('Commandes/Show', ['commande'=>$commande]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Commande $commande)
    {
        return Inertia::render('Commandes/Edit', ['commande'=>$commande]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommandeRequest $request, Commande $commande)
    {
        $commande->update($request->all());
        $ids = $commande->produits->pluck('id');
        //$commande->produits()->sync([]);
        $commande->montantPaye=0;
        foreach ($request->produits as $produit) {
            $data = $produit['entree'];
            $data['expirationDate'] =substr($data['expirationDate'],0,10);
            //Checking if the id dosen't exists in the relation
            if( !$ids->contains($produit['id'])){
                $commande->produits()->attach($produit['id']);
            }else{
                //ajouter la différence entre la nouvelle et l'ancienne qte à la qté achetée
                //$data['qte_achete'] += $produit['entree']['qte'] - $commande->produits()->where('produit_id',$produit['id'])->first()->pivot->qte ;
            }
            $data['qte_achete'] = $produit['entree']['qte'];
            $commande->montantPaye += $data['qte_achete'] * $data['prix_achat'];
            unset($data['created_at']);
            $commande->produits()->updateExistingPivot($produit['id'],$data);
        }
        $commande->update();
        return redirect()->route('commandes.index')->banner('Commande modifier avec succes');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Commande $commande)
    {
        $commande->delete();
    }
    private function insertProducts(Array $produits,  Commande $commande, bool $isEdit=false)
    {
        foreach ($produits as $produit) {
            $data = $produit['entree'];

                $data['qte_achete'] = $produit['entree']['qte'];

            $commande->montantPaye += $data['qte'] * $data['prix_achat'];
            $commande->produits()->attach($produit['id']);
            unset($produit['entree']['created_at']);
            $commande->produits()->updateExistingPivot($produit['id'],$data);
        }
    }

        public function search(\Illuminate\Http\Request $request)
    {
        $items = Commande::search();
        return $items->paginate(10);
    }

}
