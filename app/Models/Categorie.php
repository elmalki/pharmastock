<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;
    protected $fillable = ['label'];
    protected $appends = ['total'];
    public function produits()
    {
        return $this->hasMany(Produit::class);
    }

    public function getTotalAttribute(){
        return $this->produits()->count();
    }
}
