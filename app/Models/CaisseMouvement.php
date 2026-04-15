<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaisseMouvement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['type', 'montant', 'motif', 'vente_id', 'user_id'];

    protected $casts = [
        'montant' => 'decimal:2',
    ];

    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function soldeActuel(): float
    {
        $row = static::selectRaw(
            "COALESCE(SUM(CASE WHEN type='entree' THEN montant ELSE -montant END), 0) as solde"
        )->first();
        return (float) ($row->solde ?? 0);
    }
}
