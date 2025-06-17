<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidatureEmploi extends Model
{
    protected $fillable = [
        'id',
        'cv',
        'cip',
        'diplome',
        'offre_emploi_id',
        'ouvrier_id',
        'statut',

    ];
    public function ouvrier()
    {
        return $this->belongsTo(Ouvrier::class, 'ouvrier_id');
        
    }

    public function offre()
    {
        return $this->belongsTo(OffreEmploi::class, 'offre_emploi_id');
    }

}
