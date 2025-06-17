<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partenaire extends Model
{
    protected $fillable = [
        'id',
        'secteur',
        'description',
        'adresse',
        'logo',
        'contact',
        'IFU',
        'RCCM',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
    public function produits()
    {
        return $this->hasMany(Produit::class);
    }
}
