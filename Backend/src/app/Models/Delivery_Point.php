<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery_Point extends Model
{
    protected $table = 'delivery_points';
    //HE TENID QUE ÑADIR ESTA LINEA SINO NO PODIA CREAR UN PUNTO DE ENTREGA PARA PROBAR EL MAPA Y NO ME DEJABA
    public $timestamps = false;
    protected $primaryKey = 'id_delivery_point';
    protected $fillable = [
        'id_user',
        'name',
        'direction',
        'latitude',
        'length',
    ];
    protected $hidden = [
        'id_user',
    ];

    public function products()
    {
        // Relación: Este Punto de Entrega "tiene muchos" (hasMany) Productos.
        // Le indicamos a Laravel que busque los productos donde la columna 
        // 'id_delivery_point' coincida con el ID de este punto.
        return $this->hasMany(Product::class, 'id_delivery_point','id_delivery_point');
    }
    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }

} 
