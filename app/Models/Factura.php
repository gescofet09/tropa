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

    public function pedido(){
        return $this->belongsTo(Pedido::class);
    }
}
