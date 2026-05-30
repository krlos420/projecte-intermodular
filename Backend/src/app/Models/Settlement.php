<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'payer_id',
        'receiver_id',
        'house_id',
        'amount',
    ];

    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id', 'id_user');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id_user');
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }
}
