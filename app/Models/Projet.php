<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projet extends Model
{
    protected $fillable = [
        'id',
        'titre',
        'description',
        'date_debut',
        'date_fin',
        'lieu',
        'entreprise_id',
        'client_id'
    ];

    public function entreprise(){
       return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    public function client(){
       return $this->belongsTo(Client::class, 'client_id');
    }
}
