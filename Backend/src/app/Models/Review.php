<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
    public $timestamps = false;
    protected $primaryKey = 'id_review';
    protected $fillable = [
        'id_sale',
        'calification',
        'comment',
        'review_date',
    ];
    protected $hidden = [
        'id_sale',
    ];
    public function sales(){
        return $this-> hasMany(Sale::Class,'id_review','id_review');
    }
}
