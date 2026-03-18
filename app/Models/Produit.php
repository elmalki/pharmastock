<?php

namespace App\Models;

use App\Notifications\Stock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Milon\Barcode\DNS1D;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Produit extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;
    protected $fillable = [
        'barcode', 'label', 'dci', 'forme', 'dosage', 'laboratoire',
        'unite', 'description', 'perissable', 'ordonnance_requise',
        'prix_public', 'limit_command', 'generated', 'categorie_id',
    ];
    protected $with = ['categorie'];
    protected $appends = ['qte', 'qtePerime'];

    public function commandes()
    {
        return $this->belongsToMany(Commande::class, 'commande_produit', 'produit_id','commande_id')->withPivot('n_lot', 'qte','qte_achete','tva', 'prix_achat','prix_vente', 'expirationDate')->withTimestamps();
    }

    public function lots()
    {
        return $this->hasMany(CommandeProduit::class, 'produit_id');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class,'categorie_id');
    }

    public function enRupture(): bool
    {
        $qte = $this->lots_sum_qte ?? $this->lots()->sum('qte');
        return $qte < ($this->attributes['limit_command'] ?? 0);
    }

    public function getQteAttribute()
    {
        return $this->lots_sum_qte ?? $this->lots()->sum('qte');
    }

    public function getQtePerimeAttribute()
    {
        return $this->lots_perime_sum ?? $this->lots()
            ->where('expirationDate', '<', Carbon::now())
            ->sum('qte');
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
