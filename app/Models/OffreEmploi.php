<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffreEmploi extends Model
{
    protected $fillable = [
        'id',
        'projet',
        'description',
        'lieu',
        'date_limite',
        'entreprise_id',

    ];


    public function candidatureEmplois()
    {
        return $this->hasMany(CandidatureEmploi::class);
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }


    
}
