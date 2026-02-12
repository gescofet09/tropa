<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = ["nombre", "precio", "stock"];

    public function pedidos(){
        return $this->belongsToMany(Pedido::class, "detalle_pedido")->withPivot("cantidad", "precio_unitario");
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}
