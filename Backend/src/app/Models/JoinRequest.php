<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoinRequest extends Model
{
    protected $fillable = ['user_id', 'house_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }
}
