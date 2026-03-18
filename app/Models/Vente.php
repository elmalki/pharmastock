<?php

namespace App\Models;

use App\Enums\ModePaiement;
use App\Enums\StatutVente;
use App\Filters\ClientId;
use App\Filters\DateDébut;
use App\Filters\DateFin;
use App\Filters\NBon;
use App\Filters\Paiement;
use App\Models\Produit;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vente extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'n_facture', 'date', 'paiement', 'dateEcheance', 'ordonnance',
        'montantPaye', 'subtotal', 'remise', 'remise_amount', 'reste',
        'statut', 'benefice', 'client_id', 'user_id',
    ];
    protected $with = [];
    protected $appends = ['total'];

    protected $casts = [
        'paiement' => ModePaiement::class,
        'statut' => StatutVente::class,
        'date' => 'date',
        'dateEcheance' => 'date',
        'subtotal' => 'decimal:2',
        'montantPaye' => 'decimal:2',
        'remise_amount' => 'decimal:2',
        'reste' => 'decimal:2',
        'benefice' => 'decimal:2',
    ];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'vente_produit', 'vente_id', 'produit_id')->withPivot('lot_id', 'prix_achat', 'prix', 'qte', 'remise', 'tva')->withTimestamps();
    }
    public function getTotalAttribute()
    {
        return ($this->subtotal ?? 0) - ($this->remise_amount ?? 0);
    }

    public function getTva()
    {
        return collect($this->produits)
            ->reduce(function ($carry, $item) {
                return $carry + ($item->pivot->prix ? $item->pivot->prix * $item->pivot->qte : $item->pivot->qte * $item->prix) * $item->pivot->tva * 0.01;
            }, 0);
    }
    public static function search()
    {
        $pipes = [
            NBon::class,
            ClientId::class,
            DateDébut::class,
            DateFin::class,
            Paiement::class
        ];
        return  app(Pipeline::class)
            ->send(Vente::query())
            ->through($pipes)
            ->thenReturn();
    }
}
