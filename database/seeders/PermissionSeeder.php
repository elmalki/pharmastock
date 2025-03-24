<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Produit','Destockage','Catégorie','Fournisseur','Client','Approvisionnement','Vente'] as $permission) {
            Permission::create(['name' => 'Ajouter '.$permission,'guard_name'=>'sanctum']);
            Permission::create(['name' => 'Modifier '.$permission,'guard_name'=>'sanctum']);
            Permission::create(['name' => 'Supprimer '.$permission,'guard_name'=>'sanctum']);
            Permission::create(['name' => 'Lister '.$permission,'guard_name'=>'sanctum']);
        }
    }
}
