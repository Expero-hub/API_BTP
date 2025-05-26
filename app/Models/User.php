<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'nom',
        'prenom',
        'telephone',
        'type',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    //unicité et identité de l'ouvrier dans less tables user et ouvrier
    public function ouvrier()
    {
        return $this->hasOne(Ouvrier::class, 'id');
    }
    
    //unicité et identité de l'entreprise dans les tables user et entreprise et en meme temps relatin 1 à 1 qui les lie 
    public function entreprise()
    {
        return $this->hasOne(Entreprise::class, 'id');
    }
    //unicité et identité du stagiaire dans les tables user et stagiaire et en meme temps relatin 1 à 1 qui les lie 
    public function stagiaire()
    {
        return $this->hasOne(Stagiaire::class, 'id');
    }
    
    
    //unicité et identité du partenaire dans les tables user et partenaire et en meme temps relatin 1 à 1 qui les lie 
    public function partenaire()
    {
        return $this->hasOne(Partenaire::class, 'id');
    }
    
    //unicité et identité du client dans les tables user et client et en meme temps relatin 1 à 1 qui les lie 
    public function client()
    {
        return $this->hasOne(Client::class, 'id');
    }
    
    
   


    


}
