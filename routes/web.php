<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('dashboard');
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
       // 'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/decharge/{destockage}',[\App\Http\Controllers\DestockageController::class,'decharge'])->name('destockages.decharge');
    Route::get('commandes/search',function(){
        return Inertia::render('Commandes/Search',['fournisseurs'=>\App\Models\Fournisseur::all()]);
    })->name('commandes.search');
    Route::post('commandes/search',[\App\Http\Controllers\CommandeController::class,'search']);
    Route::get('/dashboard',[\App\Http\Controllers\DashboardController::class,'index'])->name('dashboard');
    Route::resource('categories', CategoryController::class);
    Route::resource('fournisseurs', \App\Http\Controllers\FournisseurController::class);
    Route::resource('clients', \App\Http\Controllers\ClientController::class);
    Route::resource('ventes', \App\Http\Controllers\VenteController::class);
    Route::resource('produits', \App\Http\Controllers\ProduitController::class);
    Route::resource('commandes', \App\Http\Controllers\CommandeController::class);
    Route::resource('destockages', \App\Http\Controllers\DestockageController::class);
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::resource('roles', \App\Http\Controllers\RoleController::class);
    Route::resource('permissions', \App\Http\Controllers\PermissionController::class);
    Route::get('export', [\App\Http\Controllers\ProduitController::class,'export']);
    Route::get('exportPerimes', [\App\Http\Controllers\ProduitController::class,'exportPerimes']);
    Route::post('stock', [\App\Http\Controllers\ProduitController::class,'stock']);
    Route::post('barcodes', [\App\Http\Controllers\ProduitController::class,'barcodes']);
    Route::get('notifications',[\App\Http\Controllers\DashboardController::class,'notifications'])->name('notifications.index');
    Route::delete('notifications/all',[\App\Http\Controllers\DashboardController::class,'deleteAllNotifications'])->name('notifications.markasread');
    Route::get('markasread',[\App\Http\Controllers\DashboardController::class,'markAllAsRead'])->name('notifications.deleteAll');
    Route::delete('notifications/{notification_id}',[\App\Http\Controllers\DashboardController::class,'notificationDelete'])->name('notifications.delete');
    Route::get('peremption',function (){
        return Inertia::render('Produits/Peremption');
    })->name('produits.peremption');
    Route::get('perimes',[\App\Http\Controllers\ProduitController::class,'perimes'])->name('produits.perimes');
    Route::post('perimesPDF',[\App\Http\Controllers\ProduitController::class,'perimesPDF'])->name('produits.perimesPDF');
    Route::get('echeances',function (){
        return Inertia::render('Calendar');
    })->name('echeances');
    Route::get('test',function (){
        return Inertia::render('Test');
    });
    Route::get('activity',function (){
        return \Spatie\Activitylog\Models\Activity::causedBy(auth()->user())->get()->first()->causer()->name;
    });

});
