<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidatureSousTraitance extends Model
{
    protected $fillable = [
        'entreprise_id',
        'sous_traitance_id',
        'motivation',
    ];

     public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
        
    }

    public function sousTraitance()
    {
        return $this->belongsTo(SousTraitance::class, 'sous_traitance_id');
    }
}
