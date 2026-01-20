<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'registration_date',
        'house_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'registration_date' => 'date',
    ];

    // Piso donde vive el usuario
    public function house()
    {
        return $this->belongsTo(House::class, 'house_id');
    }

    // Gastos pagados por este usuario
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'payer_id', 'id_user');
    }
}
