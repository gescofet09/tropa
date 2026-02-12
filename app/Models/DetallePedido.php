<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    // Tabla asociada
    protected $table = 'detalle_pedidos';

    // Columnas que se pueden asignar masivamente
    protected $fillable = [
        'pedido_id',
        'producto_id',
        'cantidad',
        'preparado', // <- importante para tu checkbox
    ];

    // Si no tienes timestamps en la tabla pivote
    public $timestamps = true;

    // Relación al pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    // Relación al producto
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
