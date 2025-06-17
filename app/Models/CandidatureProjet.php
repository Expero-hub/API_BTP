<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidatureProjet extends Model
{
    protected $fillable = [
        'entreprise_id',
        'projet_id',
        'motivation',
        'statut',
    ];

    public function projet()
{
    return $this->belongsTo(Projet::class);
}

public function entreprise()
{
    return $this->belongsTo(Entreprise::class);
}

}
