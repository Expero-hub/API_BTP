<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ouvrier extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false; // important

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
        protected $fillable = [
            'id',
            'metier',
            'cv',
            'diplome',
            'certifications',
            
        ];

        public function candidaturesEmploi()
    {
        return $this->hasMany(CandidatureEmploi::class);
    }

}
