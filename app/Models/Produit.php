<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $fillable = [
        'partenaire_id',
        'nom',
        'prix',
        'description',
        'type',
        'photo',
    ];

     public function commande_produit()
    {
        return $this->hasMany(CandidatureEmploi::class);
    }

    public function partenaire()
    {
        return $this->belongsTo(Partenaire::class, 'partenaire_id');
    }
}
