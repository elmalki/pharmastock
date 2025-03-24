<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFournisseurRequest;
use App\Http\Requests\UpdateFournisseurRequest;
use App\Models\Fournisseur;
use Inertia\Inertia;

class FournisseurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Fournisseurs/Index', ['items'=>Fournisseur::all()]);
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
    public function show(Fournisseur $fournisseur)
    {

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
