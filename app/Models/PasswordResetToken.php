<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PasswordResetToken extends Model
{
    

    protected $fillable = [
        
        'user_id',
        'code',
        'expires_at',
    ];

    protected $dates = [
        'expires_at',
        'created_at',
    ];

    public $timestamps = false; // Désactiver les timestamps automatiques

    

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}