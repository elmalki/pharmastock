<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('produits',function (){
    return \App\Models\Produit::all();
});
Route::post('peremption',function (){
    return \App\Models\CommandeProduit::with('produit')->where('qte','>',0)->whereBetween('expirationDate',[request()->start,request()->end])->get();
});
Route::post('commandes',function (){
    return \App\Models\Commande::whereBetween('dateEcheance',[request()->start,request()->end])->get();
});
Route::get('fournisseurs',function (){
    return \App\Models\Fournisseur::all();
});
Route::get('clients',function (){
    return \App\Models\Client::all();
});
Route::post('getLots',function (){
    return \App\Models\CommandeProduit::where('qte','>',0)->where('produit_id',request()->id)->get();
});
