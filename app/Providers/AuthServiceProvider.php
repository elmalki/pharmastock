<?php

namespace App\Providers;

use App\Models\Categorie;
use App\Models\Produit;
use App\Policies\CategoriePolicy;
use App\Policies\ProduitPolicy;
use \Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
   /* protected $policies = [
        Categorie::class=>CategoriePolicy::class,
        Produit::class=>ProduitPolicy::class,
    ];*/
    /**
     * Register services.
     */
    public function register(): void
    {
        //Gate::policy(Categorie::class,CategoriePolicy::class);
        //Gate::policy(Produit::class,ProduitPolicy::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
       // $this->registerPolicies();
    }
}
