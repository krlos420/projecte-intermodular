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
        'house_id',
    ];

    protected $casts = [
        'registration_date' => 'date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Piso donde vive el usuario
    public function house()
    {
        return $this->belongsTo(House::class, 'house_id');
    }

    // Gastos pagados por este usuario
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'payer_id');
    }
}
