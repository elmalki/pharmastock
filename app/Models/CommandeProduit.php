<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CommandeProduit extends Pivot
{
    protected $table = 'commande_produit';
   // protected $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime'];
    function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }
}
