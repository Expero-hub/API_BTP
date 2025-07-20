<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntrepriseProjet extends Model
{
    protected $fillable = [
        'projet_id',
        'entreprise_id',
    ];

    public function entreprise()
{
    return $this->belongsTo(Entreprise::class);
}

    public function projet()
{
    return $this->belongsTo(Projet::class, 'projet_id');
}

}
