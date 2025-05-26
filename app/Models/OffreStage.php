<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffreStage extends Model
{
    protected $fillable = [
        'id',
        'domaine',
        'description',
        'niveau',
        'date_limite',
        'entreprise_id',

    ];


    public function candidatureStage()
    {
        return $this->hasMany(CandidatureStage::class);
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }
}
