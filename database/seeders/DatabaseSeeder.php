<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Zona;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //* zonas

        $zona1 = Zona::create([
            'nombre' => 'Playa Blanca',
            'codigo_postal' => '35580'
        ]);

        $zona2 = Zona::create([
            'nombre' => 'Haría',
            'codigo_postal' => '35520'
        ]);

        $zona3 = Zona::create([
            'nombre' => 'Costa Teguise',
            'codigo_postal' => '35508'
        ]);

        
        $zona4 = Zona::create([
            'nombre' => 'Arrecife',
            'codigo_postal' => '35500'
        ]);

        $zona5 = Zona::create([
            'nombre' => 'Puerto del Carmen',
            'codigo_postal' => '35510'
        ]);

        //* admin
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@tropa.com',
            'password' => Hash::make('admin.pass'),
            'rol' => 'admin',
            'zona_id' => null
        ]);

        //* repartidor
        $repartidor = User::create([
            'name' => 'repartidor',
            'email' => 'repartidor@tropa.com',
            'password' => Hash::make('repartidor.pass'),
            'rol' => 'repartidor',
        ]);

        $repartidor->zonas()->attach([$zona1->id]);

        $cliente = User::create([
            'name' => 'cliente',
            'email' => 'cliente@tropa.com',
            'password' => Hash::make('cliente.pass'),
            'rol' => 'cliente',
            'zona_id' => $zona1->id
        ]);

    }
}
