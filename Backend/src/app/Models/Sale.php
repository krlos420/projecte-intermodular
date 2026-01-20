<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sale extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'id_sale';
    public $timestamps = false;

    protected $fillable = [
        'id_product',
        'id_buyer',
        'id_seller',
        'id_delivery_point',
        'sale_date',
        'total',
        'collection_date'
    ];

    protected $hidden = [
        'id_product',
        'id_buyer',
        'id_seller',
        'id_delivery_point',
        'id_review',
    ];
     protected $casts = [
        'sale_date' => 'date',
        'collection_date' => 'date',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_product');
    }
    public function buyer(){
    return $this->belongsTo(User::class,'id_buyer','id_user');
    }
    public function seller(){
    return $this->belongsTo(User::class,'id_seller','id_user');
    }
    public function delivery_point()
{
    return $this->belongsTo(
        Delivery_Point::class, 'id_delivery_point', 'id_delivery_point'
    );
}
    public function reviews(){
    return $this->hasMany(Review::class,'id_sale','id_sale');
    }

}
