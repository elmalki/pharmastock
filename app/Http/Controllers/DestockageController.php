<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDestockageRequest;
use App\Http\Requests\UpdateDestockageRequest;
use App\Models\CommandeProduit;
use App\Models\Destockage;
use App\Models\Produit;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\DashboardNotification;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DestockageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Destockages/Index', ['items' => Destockage::with('produits', 'user')->paginate(10),'sort_fields'=>request()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $setting = Setting::latest()->first();
        if(!$setting || date('Y')!=$setting->year){
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
        DB::beginTransaction();

        try {
            $setting = Setting::latest()->first();
            if (!$setting || date('Y') != $setting->year) {
                $setting = Setting::create();
                $setting->refresh();
            }
            $setting->destockage_number = $setting->destockage_number + 1;
            $setting->save();

            $destockage = Destockage::create([
                'n_destockage' => $setting->destockage_number . '/' . $setting->year,
                'motifs' => $request->motifs,
                'user_id' => Auth::id(),
            ]);

            $total = 0;
            foreach ($request->produits as $produit) {
                foreach ($produit['lots'] as $lotData) {
                    if ($lotData['sortie'] == 0) {
                        continue;
                    }

                    $lot = CommandeProduit::where('produit_id', $lotData['produit_id'])
                        ->where('commande_id', $lotData['commande_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$lot || $lot->qte < $lotData['sortie']) {
                        $lotName = $lot?->n_lot ?? 'inconnu';
                        throw new \Exception("Stock insuffisant pour le lot {$lotName}.");
                    }

                    $total += $lotData['sortie'];
                    $destockage->produits()->attach($lotData['produit_id'], ['qte' => $lotData['sortie']]);
                    $lot->decrement('qte', $lotData['sortie']);

                    $produitModel = Produit::find($lotData['produit_id']);
                    if ($produitModel->enRupture()) {
                        User::all()->each(function (User $user) use ($produitModel) {
                            $user->notify(new DashboardNotification($produitModel));
                        });
                    }
                }
            }

            if ($total == 0) {
                $destockage->delete();
            }

            DB::commit();

            return Redirect::route('destockages.index')->banner('Destockage effectué avec succès!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Erreur lors du destockage: ' . $e->getMessage()])
                ->withInput();
        }
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

    public function yearPdf()
    {
        $year = (int) (request('year') ?: date('Y'));
        $destockages = Destockage::with('produits', 'user')
            ->whereYear('created_at', $year)
            ->orderBy('created_at')
            ->get();

        return \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.destockages',
            ['destockages' => $destockages, 'year' => $year]
        )->setPaper('A4', 'landscape')->stream("destockages_{$year}.pdf");
    }

    public function decharge(Destockage $destockage)
    {
        return \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.decharge',
            ['destockage'=>$destockage->load('produits', 'user')]
        )
            // ->setPaper('A4', 'landscape')
            ->stream();
    }
}
