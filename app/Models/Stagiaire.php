<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stagiaire extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false; // important

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
        protected $fillable = [
            'id',
            'specialite',
            'cv',
            'diplome',
            'certifications',
          
        ];
        

        public function candidaturesStage()
    {
        return $this->hasMany(CandidatureStage::class);
    }
}
