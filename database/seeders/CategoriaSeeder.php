<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Categoria::create(['nombre' => 'QUESOS']);
        Categoria::create(['nombre' => 'FIAMBRES']);
        Categoria::create(['nombre' => 'HUEVOS']);
        Categoria::create(['nombre' => 'LÁCTEOS']);
        Categoria::create(['nombre' => 'OTROS']);
    }
}
