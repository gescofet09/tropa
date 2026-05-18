<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = ["nombre", "precio", "stock", "categoria_id", "unidad"];

    public function setNombreAttribute($value){
        $this->attributes['nombre'] = mb_strtoupper($value, 'UTF-8');
    }

    public function pedidos(){
        return $this->belongsToMany(Pedido::class, "detalle_pedidos")->withPivot("cantidad", "precio_unitario", "preparado");
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}
