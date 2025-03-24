<?php

namespace App\Models;

use App\Filters\ClientId;
use App\Filters\DateDébut;
use App\Filters\DateFin;
use App\Filters\NBon;
use App\Filters\Paiement;
use App\Models\Produit;
use Illuminate\Routing\Pipeline;
use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    protected $fillable = ['date', 'paiement', 'dateEcheance', 'diagnostic', 'observation', 'montantPaye','ordonnance', 'cheque_id', 'client_id'];
    protected $with = [ 'produits'];
    protected $appends = ['remise', 'frais', 'reste','benefice'];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'vente_prodtuits', 'vente_id', 'produit_id')->withPivot('prix_achat', 'prix', 'qte', 'remise', 'tva','commande_id')->withTimeStamps();
    }
    public function getResteAttribute()
    {
        $reste = $this->attributes['fraisMedicament'] - $this->attributes['montantPaye'] - $this->remise;
        return $reste>=0.01?$reste:0;
    }
    public function getRemiseAttribute()
    {
        return collect($this->medicaments)
            ->reduce(function ($carry, $item) {
                return $carry + ($item->pivot->prix ? $item->pivot->prix*$item->pivot->qte : $item->pivot->qte * $item->prix) * $item->pivot->remise * 0.01;
            }, 0);
    }

    public function getTva()
    {
        return collect($this->medicaments)
            ->reduce(function ($carry, $item) {
                return $carry + ($item->pivot->prix ? $item->pivot->prix*$item->pivot->qte : $item->pivot->qte * $item->prix) * $item->pivot->tva * 0.01;
            }, 0);
    }

    public function getBeneficeAttribute()
    {
        return collect($this->medicaments)
            ->reduce(function ($carry, $item) {
                return $carry +  $item->pivot->qte * ( $item->pivot->prix * (1-$item->pivot->remise * 0.01))-$item->pivot->prix_achat;
            }, 0);
    }
    public function getFraisAttribute()
    {
        return (float)$this->attributes['fraisMedicament'];
    }
    public function recette()
    {
        return $this->hasOne('App\Recette');
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
    public function recupererLesEntrees()
    {
        /*
        foreach ($this->medicaments as $medicament) {
            if($medicament->pivot->entrees) {
                $entrees = json_decode($medicament->pivot->entrees, JSON_OBJECT_AS_ARRAY);
                if ($entrees) {
                    foreach ($entrees as $id => $qte) {
                        $entree = Entree::withTrashed()->find($id);
                        $entree->qte += $qte;
                        $entree->update();
                        $entree->restore();
                    }
                }
            }
        }*/
    }
}
