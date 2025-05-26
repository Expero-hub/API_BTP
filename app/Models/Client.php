<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function projets()
    {
        return $this->hasMany(Projet::class);
    }
}
