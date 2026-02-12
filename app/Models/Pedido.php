<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = ['usuario_id','repartidor_id','estado','total'];

    public function cliente(){ 
        return $this->belongsTo(User::class,'usuario_id'); 
    }
    public function repartidor(){ 
        return $this->belongsTo(User::class,'repartidor_id'); 
    }
    public function productos(){ 
        return $this->belongsToMany(Producto::class,'detalle_pedidos')->withPivot('cantidad','precio_unitario', 'preparado'); 
    }
    public function albaran(){ 
        return $this->hasOne(Albaran::class); 
    }
    public function factura(){ 
        return $this->hasOne(Factura::class); 
    }
}

