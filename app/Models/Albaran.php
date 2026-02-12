<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Albaran extends Model
{
    protected $fillable = [
        'pedido_id',
        'fecha',
        'archivoPDF'
    ];

    public function pedido(){
        return $this->belongsTo(Pedido::class);
    }
}
