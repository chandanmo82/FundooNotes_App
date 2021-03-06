<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function setFirstnameAttribute($value) {
         $this->attributes['firstname'] = ucfirst($value);
    }

    public function getLastnameAttribute($value) {
        return $this->attributes['lastname'] = ucfirst($value);
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
      /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function notes()
    {
        return $this->hasMany('App\Models\Note');
    }
    public function labels()
    {
        return $this->hasMany('App\Models\Label');
    }
    public function labelnote()
    {
        return $this->hasMany('App\Models\LabelNote');
    }
    public function collaborators()
    {
        return $this->hasMany('App\Models\Collaborator');
    }

}