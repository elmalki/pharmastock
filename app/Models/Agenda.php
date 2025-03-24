<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;
    protected $fillable = ['title','description', 'degree', 'debut', 'fin', 'hdebut', 'hfin', 'isRecurrent', 'startRecur','qte','unit','','client_id'];
    protected $appends = ['color', 'start', 'end'];
    protected $with = ['agenda'];
    public function getColorAttribute()
    {
        $classes = ['Urgent' => 'red', 'Moyen' => 'orange', 'Ordinaire' => 'teal'];
        return isset($this->attributes['degree']) ? $classes[$this->attributes['degree']] : 'teal';
    }
    public function getHdebutAttribute($value)
    {
        return substr($value, 0, 5);
    }
    public function getHfinAttribute($value)
    {
        return substr($value, 0, 5);
    }
    public function getStartAttribute()
    {
        return $this->attributes['debut'] . ' ' . substr($this->attributes['hdebut'], 0, 5);
    }
    public function getEndAttribute()
    {
        return isset($this->attributes['fin']) ? $this->attributes['fin'] . ' ' . substr($this->attributes['hfin'], 0, 5) : null;
    }

    public function agenda()
    {
        return $this->belongsTo(Agenda::class, 'event_id');
    }

    public function body(){
        return $this->client?$this->client->contact." ".($this->client->tel):' '.'<br>'.$this->description;
    }
}
