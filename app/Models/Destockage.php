<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Destockage extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','motifs','fonctionnaire','n_destockage'];
    protected $with = ['produits','user'];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function produits():BelongsToMany{
        return $this->belongsToMany(Produit::class,'destockage_produit','destockage_id','produit_id')->withPivot('qte')->withTimestamps();
    }
}
