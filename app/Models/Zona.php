<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $fillable = ["nombre", "codigo_postal"];

    public function usuarios(){
        return $this->hasMany(User::class);
    }

    public function pedidos(){
        return $this->hasMany(Pedido::class);
    }

    public function repartidores(){
        return $this->belongsToMany(User::class, 'repartidor_zona', 'zona_id', 'repartidor_id')->where('rol', 'repartidor');
    }
}
