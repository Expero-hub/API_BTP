<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SousTraitance extends Model
{
   protected $fillable = [
        'projet_id',
        'tache',
        'entreprise_maitre_id',
        'entreprise_sous_traitante_id',
        'date_debut',
        'date_fin',
        'mode',
        'statut',
   
    ];

    public function projet(){
       return $this->belongsTo(Projet::class);
    }

    public function entreprise_maitre(){
       return $this->belongsTo(Entreprise::class, 'entreprise_maitre_id');
    }
    public function entreprise_sous_triatante(){
       return $this->belongsTo(Entreprise::class, 'entreprise_sous_traitante_id');
    }
}
