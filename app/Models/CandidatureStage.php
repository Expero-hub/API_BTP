<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidatureStage extends Model
{
    protected $fillable = [
        'id',
        'cv',
        'cip',
        'diplome',
        'lettre_motivation',
        'offre_stage_id',
        'stagiaire_id',

    ];
    public function stagiaire()
    {
        return $this->belongsTo(Stagiaire::class, 'stagiaire_id');
        
    }

    public function offre()
    {
        return $this->belongsTo(OffreStage::class, 'offre_stagiaire_id');
    }
}
