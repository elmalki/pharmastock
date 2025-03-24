<?php

namespace App\Models;

use App\Filters\FournisseurId;
use App\Filters\NBon;
use App\Filters\NFacture;
use App\Notifications\Expired;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Pipeline;
use phpDocumentor\Reflection\Types\This;

class Entree extends Model
{
    use HasFactory;
    protected $fillable = ['qte', 'n_bon', 'n_facture', 'expirationDate','tva', 'produit_id', 'fournisseur_id', 'n_lot', 'prix_achat','prix_vente'];
    protected $with = ['fournisseur','produit'];
    protected $appends = ['days'];

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }
    public function commande()
    {
        return $this->belongsTo(Commande::class, 'commande_id');
    }

    public function getDaysAttribute()
    {
        return $this->attributes['expirationDate'] ? Carbon::now()->diffInDays(Carbon::createFromFormat('Y-m-d', $this->attributes['expirationDate']), false) : null;
    }
    public static function search()
    {
        $pipes = [
            NFacture::class,
            NBon::class,
            FournisseurId::class
        ];
        return app(Pipeline::class)
            ->send(self::query())
            ->through($pipes)
            ->thenReturn();
    }
    public function lotPerime($jour=0)
    {
        return $this::where('expirationDate','<',Carbon::now()->addDays($jour))->first();
    }
    public function checkAndNotify()
    {
        if($this->lotPerime(4)){
            User::all()->each(function($user,$key){
                if($user->expiredNotificationDoesntExists($this->id))
                    $user->notify(new Expired($this));
            });
        }
    }
}
