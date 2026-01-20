<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';
    protected $fillable = [
        'id_product',
        'id_transmitter',
        'id_receiver',
        'content',
        'shipping_date'
    ];
}
