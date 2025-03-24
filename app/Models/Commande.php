<?php

namespace App\Models;

use App\Filters\StartDate;
use App\Filters\EndDate;
use App\Filters\NBon;
use App\Filters\NFacture;
use App\Filters\FournisseurId;
use Carbon\Carbon;
use Dompdf\Css\Content\Attr;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = ['n_bon','n_facture','fournisseur_id','date','dateEcheance','paiement','situation'];
    protected $with = ['produits','fournisseur'];
    //protected $appends = ['total'];

    public function date():Attribute
    {
        return Attribute::make(set:fn($value)=>Carbon::parse($value)->format('Y-m-d'));
    }
    public function dateEcheance():Attribute
    {
        return Attribute::make(set:fn($value)=>Carbon::parse($value)->format('Y-m-d'));
    }
    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'commande_produit', 'commande_id','produit_id')->withPivot('n_lot', 'qte','qte_achete','tva', 'prix_achat','prix_vente', 'expirationDate')->withTimestamps();
    }
    public function fournisseur(){
        return $this->belongsTo(Fournisseur::class);
    }
    public static function search()
    {
        $pipes = [
            //Entity::class,
            StartDate::class,
            EndDate::class,
            NBon::class,
            FournisseurId::class,
            NFacture::class,
        ];
        return app(Pipeline::class)
            ->send(Commande::query())
            ->through($pipes)
            ->thenReturn()
            ->orderBy('n_bon')
            ->with('produits','fournisseur');
    }
}
