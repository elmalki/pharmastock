<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    use HasFactory;
    protected $fillable = ['societe','contact','telephone','adresse','email'];

    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }
}
