<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MouvementStock extends Model
{
    protected $table = 'mouvements_stock';

    protected $fillable = [
        'lot_id',
        'produit_id',
        'user_id',
        'type',
        'reference_type',
        'reference_id',
        'quantite',
        'stock_avant',
        'stock_apres',
        'raison',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'stock_avant' => 'integer',
        'stock_apres' => 'integer',
    ];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(CommandeProduit::class, 'lot_id');
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
