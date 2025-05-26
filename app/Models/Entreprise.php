<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false; // important
    protected $fillable = [
        'id',
        'nom_entreprise',
        'domaine',
        'logo',
        'IFU',
        'RCCM',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    //entreprise-offre
    public function offresEmploi()
    {
        return $this->hasMany(OffreEmploi::class);
    }
    public function offresStage()
    {
        return $this->hasMany(OffreStage::class);
    }
    public function projets() {
        return $this->hasMany(Projet::class);
    }
}
