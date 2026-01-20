<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo House - Representa un piso/casa compartida.
 * 
 * Relaciones:
 * - Un House tiene muchos Users (los compañeros de piso)
 * - Un House tiene muchos Expenses (los gastos del piso)
 */
class House extends Model
{
    // Campos que se pueden rellenar masivamente (ej: House::create([...]))
    protected $fillable = [
        'name',
        'invite_code',
    ];

    /**
     * Relación: Una casa tiene muchos usuarios (compañeros de piso).
     * Tipo: One-to-Many (1:N)
     * 
     * Ejemplo de uso: $house->users devuelve todos los usuarios del piso
     */
    public function users()
    {
        return $this->hasMany(User::class, 'house_id');
    }

    /**
     * Relación: Una casa tiene muchos gastos.
     * Tipo: One-to-Many (1:N)
     * 
     * Ejemplo de uso: $house->expenses devuelve todos los gastos del piso
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'house_id');
    }
}
