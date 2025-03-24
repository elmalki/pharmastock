<?php

namespace App\Models;

use App\Notifications\Stock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Milon\Barcode\DNS1D;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Produit extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = ['label', 'description', 'perissable', 'limit_command','generated','categorie_id'];
   protected $with = ['categorie'];
    protected $appends = ['remise', 'qte', 'qtePerime'];
    public function commandes()
    {
        return $this->belongsToMany(Commande::class, 'commande_produit', 'produit_id','commande_id')->withPivot('n_lot', 'qte','qte_achete','tva', 'prix_achat','prix_vente', 'expirationDate')->withTimestamps();
    }
    public function enRupture():bool{
        return CommandeProduit::where('produit_id', $this->attributes['id'])->sum('qte')<$this->attributes['limit_command'];
    }
    public function getRemiseAttribute()
    {
        return 0;
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class,'categorie_id');
    }
    public function getQteAttribute()
    {
        return CommandeProduit::where('produit_id', $this->attributes['id'])->sum('qte');
    }

    public function getQtePerimeAttribute()
    {
        return CommandeProduit::where('produit_id', $this->attributes['id'])->where('expirationDate', '<', Carbon::now())->sum('qte');
    }
    public function lotPerime($jour = 1)
    {
        return Entree::where('produit_id', $this->attributes['id'])->where('expirationDate', '<', Carbon::now()->addDays($jour))->pluck('n_lot')->toArray();
    }
    public function checkAndNotify()
    {
        if ($this->limit_command && $this->qte < $this->limit_command) {
            User::all()->each(function ($user, $key) {
                if ($user->stockNotificationDoesntExists($this->id))
                    $user->notify(new Stock($this));
            });
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        // TODO: Implement getActivitylogOptions() method.
        return LogOptions::defaults()
            ->logAll();
    }
}
