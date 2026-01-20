<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'registration_date',
    ];
    protected $casts = [
        'registration_date' => 'date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function delivery_points()
    {
        return $this->hasMany(Delivery_Point::class, 'id_user', 'id_user');
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'id_user', 'id_user');
    }
    public function buyer()
    {
        return $this->hasMany(Sale::class, 'id_buyer', 'id_user');
    }
    public function seller()
    {
        return $this->hasMany(Sale::class, 'id_seller', 'id_user');
    }
}
