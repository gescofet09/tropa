<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 'usuarios';
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'zona_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    //* relaciones tablas
    public function zona(){
        return $this->belongsTo(Zona::class);
    }

    public function zonas(){
        return $this->belongsToMany(Zona::class, "repartidor_zona", "repartidor_id", "zona_id");
    }

    public function pedidos(){
        return $this->hasMany(Pedido::class, "usuario_id");
    }

    public function repartos(){
        return $this->hasMany(Pedido::class, "repartidor_id");
    }

    //? roles
    public function esCliente(){
        return $this->rol == "cliente";
    }
    public function esRepartidor(){
        return $this->rol == "repartidor";
    }
    public function esAdmin(){
        return $this->rol == "admin";
    }
    
}
