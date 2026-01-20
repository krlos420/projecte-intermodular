<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo House - Representa un piso compartido
class House extends Model
{
    protected $fillable = [
        'name',
        'invite_code',
    ];

    // Usuarios que viven en este piso
    public function users()
    {
        return $this->hasMany(User::class, 'house_id');
    }

    // Gastos de este piso
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'house_id');
    }
}
