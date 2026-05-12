<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $fillable = [
        'pedido_id',
        'fecha',
        'numero',
        'total',
        'archivoPDF'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'total' => 'decimal:2',
    ];

    public function pedido(){
        return $this->belongsTo(Pedido::class);
    }
}
