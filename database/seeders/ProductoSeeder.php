<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Categoria;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        //* buscar categorías existentes
        $quesos = Categoria::where('nombre', 'QUESOS')->first();
        $fiambres = Categoria::where('nombre', 'FIAMBRES')->first();
        $huevos = Categoria::where('nombre', 'HUEVOS')->first();
        $lacteos = Categoria::where('nombre', 'LÁCTEOS')->first();
        $otros = Categoria::where('nombre', 'OTROS')->first();

        $productos = [
            ['nombre' => 'QUESO FRESCO VALSEQUILLO', 'precio' => 7.60, 'stock' => 100, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO FRESCO AHUMADO VALSEQUILLO', 'precio' => 7.80, 'stock' => 80, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESOS SEMICURADOS VALSEQUILLO- SEMI', 'precio' => 9.25, 'stock' => 100, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESOS SEMICURADOS VALSEQUILLO- PIMENTON', 'precio' => 9.25, 'stock' => 100, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESOS SEMICURADOS VALSEQUILLO- AHUMADO', 'precio' => 9.25, 'stock' => 100, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESOS SEMICURADOS VALSEQUILLO- GOFIO', 'precio' => 9.25, 'stock' => 100, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO CURADO VALSEQUILLO', 'precio' => 11.15, 'stock' => 50, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO ARTESANO BLANCO FUERTE', 'precio' => 13.45, 'stock' => 50, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO ARTESANO PIMENTON FUERTE', 'precio' => 14.45, 'stock' => 50, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO ARTESANO AÑEJO', 'precio' => 16.65, 'stock' => 50, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO GOUDA PLATO', 'precio' => 7.50, 'stock' => 50, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO GOUDA O EDAM BARRA', 'precio' => 5.50, 'stock' => 50, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO MANCHEGO CAPA NEGRA', 'precio' => 7.75, 'stock' => 50, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO MANCHEGO CAPA BLANCA', 'precio' => 5.25, 'stock' => 50, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO MANCHEGO CAPA MARRÓN', 'precio' => 7.60, 'stock' => 50, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO UNTAR €/KG', 'precio' => 6.50, 'stock' => 100, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO CHEDDAR NARANJA', 'precio' => 10.25, 'stock' => 100, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO MOZARELLA BARRA', 'precio' => 5.27, 'stock' => 100, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO LONCHEADO GFV GOUDA/EDAM 1KG', 'precio' => 7.45, 'stock' => 100, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO AZUL', 'precio' => 6.80, 'stock' => 50, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO DE CABRA RULO CON PAJA', 'precio' => 8.80, 'stock' => 50, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO FRESCO VACA', 'precio' => 5.20, 'stock' => 50, 'categoria' => $quesos, 'unidad' => 'kg'],
            ['nombre' => 'QUESO FRESCO DE CABRA', 'precio' => 5.60, 'stock' => 50, 'categoria' => $quesos, 'unidad' => 'kg'],
        ];

        $productosFiambres = [
            ['nombre' => 'PATA ASADA GRANJA FLOR', 'precio' => 11.32, 'stock' => 50, 'categoria' => $fiambres, 'unidad' => 'kg'],
            ['nombre' => 'CHORIZO TEROR', 'precio' => 5.47, 'stock' => 50, 'categoria' => $fiambres, 'unidad' => 'kg'],
            ['nombre' => 'FIAMBRE DE CERDO PARIS', 'precio' => 11.00, 'stock' => 50, 'categoria' => $fiambres, 'unidad' => 'kg'],
            ['nombre' => 'LOMO ADOBADO GRANJA FLOR ENTERO', 'precio' => 17.80, 'stock' => 50, 'categoria' => $fiambres, 'unidad' => 'kg'],
            ['nombre' => 'BACON EXTRA GRANJA FLOR ENTERO', 'precio' => 3.40, 'stock' => 50, 'categoria' => $fiambres, 'unidad' => 'kg'],
            ['nombre' => 'CENTRO DE JAMON SERRANO EN MITADES', 'precio' => 0.26, 'stock' => 50, 'categoria' => $fiambres, 'unidad' => 'kg'],
            ['nombre' => 'PALETA SANDWICH 11X11', 'precio' => 0.28, 'stock' => 50, 'categoria' => $fiambres, 'unidad' => 'kg'],
            ['nombre' => 'PECHUGA DE PAVO GRANJA FLOR', 'precio' => 6.50, 'stock' => 50, 'categoria' => $fiambres, 'unidad' => 'kg'],
            ['nombre' => 'MORTADELA HOSTELERIA G. FLOR', 'precio' => 10.35, 'stock' => 50, 'categoria' => $fiambres, 'unidad' => 'kg'],
        ];

        $productosHuevos = [
            ['nombre' => 'HUEVO CASCARA CLASE M GRANEL', 'precio' => 4.20, 'stock' => 200, 'categoria' => $huevos, 'unidad' => 'ud'],
            ['nombre' => 'HUEVO CASCARA CLASE L GRANEL', 'precio' => 24.50, 'stock' => 200, 'categoria' => $huevos, 'unidad' => 'ud'],
            ['nombre' => 'HUEVO LIQUIDO', 'precio' => 5.70, 'stock' => 100, 'categoria' => $huevos, 'unidad' => 'L'],
            ['nombre' => 'HUEVO COCIDO 75UDS', 'precio' => 17.70, 'stock' => 50, 'categoria' => $huevos, 'unidad' => 'ud'],
        ];

        $productosLacteos = [
            ['nombre' => 'LECHE UHT ENTERA', 'precio' => 5.7, 'stock' => 100, 'categoria' => $lacteos, 'unidad' => 'L'],
            ['nombre' => 'LECHE UHT SEMIDESNATADA', 'precio' => 5.7, 'stock' => 100, 'categoria' => $lacteos, 'unidad' => 'pack x6'],
            ['nombre' => 'LECHE CONDENSADA 1KG', 'precio' => 17.70, 'stock' => 100, 'categoria' => $lacteos, 'unidad' => 'pack x6'],
            ['nombre' => 'DULCE DE LECHE 5KG', 'precio' => 18.95, 'stock' => 50, 'categoria' => $lacteos, 'unidad' => 'ud'],
            ['nombre' => 'YOGURT NATURAL 10KG', 'precio' => 13.60, 'stock' => 50, 'categoria' => $lacteos, 'unidad' => 'ud'],
            ['nombre' => 'YOGURT SABORES 10KG', 'precio' => 13.60, 'stock' => 50, 'categoria' => $lacteos, 'unidad' => 'ud'],
        ];

        $todos = array_merge($productos, $productosFiambres, $productosHuevos, $productosLacteos);

        foreach ($todos as $prod) {
            Producto::create([
                'nombre' => $prod['nombre'],
                'precio' => $prod['precio'],
                'stock' => $prod['stock'],
                'categoria_id' => $prod['categoria']->id,
                'unidad' => $prod['unidad'] ?? 'kg' // valor por defecto
            ]);
        }

    }
}
