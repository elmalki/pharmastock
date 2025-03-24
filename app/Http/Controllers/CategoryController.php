<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategorieRequest;
use App\Models\Categorie;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function __construct()
    {

    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         return Inertia::render('Categories/Index', ['items' => Categorie::all()]);

       // return Inertia::render('Parametres/Categories', ['items' => Categorie::all()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        return Categorie::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Categorie $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categorie $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategorieRequest $request, Categorie $category)
    {
        $category->update($request->all());
        $category->refresh();
         return $category;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categorie $category)
    {
        $category->delete();
        return to_route('categories.index')->banner('Catégorie suprimée');
    }
}
