<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo Expense - Representa un gasto compartido
class Expense extends Model
{
    protected $fillable = [
        'title',
        'amount',
        'payer_id',
        'house_id',
        'date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    // Usuario que pagó este gasto
    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    // Piso al que pertenece el gasto
    public function house()
    {
        return $this->belongsTo(House::class, 'house_id');
    }
}
