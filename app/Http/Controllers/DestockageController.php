<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDestockageRequest;
use App\Http\Requests\UpdateDestockageRequest;
use App\Models\Commande;
use App\Models\Destockage;
use App\Models\Produit;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\DashboardNotification;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DestockageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Destockages/Index', ['items' => Destockage::paginate(10),'sort_fields'=>request()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $setting = Setting::latest()->first();
        if(date('Y')!=$setting->year){
            $setting = Setting::create();
            $setting->refresh();
        }
        return Inertia::render('Destockages/Create',['number'=>($setting->destockage_number+1).'/'.$setting->year]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDestockageRequest $request)
    {
        $destockage = Destockage::create(['n_destockage'=> $request->n_destockage,'fonctionnaire'=>$request->fonctionnaire,'motifs' => $request->motifs, 'user_id' => Auth::id()]);
        $setting = Setting::latest()->first();
        $setting->destockage_number = $setting->destockage_number + 1;
        $setting->save();
        $total = 0;
        foreach ($request->produits as $produit) {
            foreach ($produit['lots'] as $lot) {
                if($lot['sortie']==0){
                    continue;
                }
                $total+=$lot['sortie'];
                $destockage->produits()->attach($lot['produit_id'], ['qte' => $lot['sortie']]);
                Commande::find($lot['commande_id'])->produits()->updateExistingPivot($lot['produit_id'], ['qte' => max($lot['qte'] - $lot['sortie'], 0)]);
                $produit = Produit::find($lot['produit_id']);
                if ($produit->enRupture()) {
                    User::all()->each(function (User $user) use ($produit) {
                        $user->notify(new DashboardNotification($produit));
                    });
                }
            }
        }
        if($total==0)
            $destockage->delete();
        return Redirect::route('destockages.index')->banner('Destockage effectué avec succès!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Destockage $destockage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Destockage $destockage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDestockageRequest $request, Destockage $destockage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Destockage $destockage)
    {
        $destockage->delete();
    }

    public function decharge(Destockage $destockage)
    {
        return \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.decharge',
            ['destockage'=>$destockage]
        )
            // ->setPaper('A4', 'landscape')
            ->stream();
    }
}
