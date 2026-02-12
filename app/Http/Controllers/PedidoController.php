<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Albaran;
use App\Models\Factura;
use App\Models\User;
use App\Models\Zona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->esAdmin()) {
            $pedidos = Pedido::with('cliente', 'repartidor', 'productos')->get();
            $categorias = Categoria::with('productos')->get();
            return view('pedidos.admin', compact('pedidos', 'categorias'));
        }

        if ($user->esRepartidor()) {
            $pedidos = Pedido::with('cliente', 'productos')
                        ->where('repartidor_id', $user->id)
                        ->get();
            return view('pedidos.repartidor', compact('pedidos'));
        }

        if ($user->esCliente()) {
            // Pedidos del cliente
            $pedidos = Pedido::with('productos')
                        ->where('usuario_id', $user->id)
                        ->get();

            // Categorías con productos (para agrupar en la vista)
            $categorias = Categoria::with('productos')->get();

            return view('pedidos.cliente', compact('pedidos', 'categorias'));
        }
    }

    // Crear un pedido (solo cliente)
    public function store(Request $request)
    {
        $user = Auth::user();

        // Solo clientes pueden crear pedidos
        if (!$user->esCliente()) {
            return redirect()->back()->with('error', 'Solo clientes pueden crear pedidos');
        }

        $productosSeleccionados = $request->input('productos', []);
        $zonaId = $user->zona_id;

        if (empty($productosSeleccionados)) {
            return redirect()->back()->with('error', 'Debes seleccionar al menos un producto');
        }

        // Buscar un repartidor que cubra la zona del cliente
        $repartidor = User::where('rol', 'repartidor')
            ->whereHas('zonas', function($query) use ($zonaId) {
                $query->where('zona_id', $zonaId);
            })
            ->first();

        if (!$repartidor) {
            return redirect()->back()->with('error', 'No hay repartidor disponible para tu zona');
        }

        // Crear el pedido
        $pedido = Pedido::create([
            'usuario_id' => $user->id,
            'estado' => 'recibido',
            'total' => 0,
            'repartidor_id' => $repartidor->id
        ]);

        $total = 0;

        // Agregar productos al pedido (tabla pivote)
        foreach ($productosSeleccionados as $productoData) {
            if (!isset($productoData['id'])) continue;

            $producto = Producto::find($productoData['id']);
            if (!$producto) continue;

            $cantidad = max(1, (int)$productoData['cantidad']); 
            $cantidad = min($cantidad, $producto->stock); // no superar stock

            // Adjuntar producto al pedido
            $pedido->productos()->attach($producto->id, [
                'cantidad' => $cantidad,
                'precio_unitario' => $producto->precio
            ]);

            // Calcular total
            $total += $producto->precio * $cantidad;

            // Reducir stock
            $producto->stock -= $cantidad;
            $producto->save();
        }

        // Guardar total del pedido
        $pedido->total = $total;
        $pedido->save();

        return redirect()->back()->with('success', 'Pedido creado correctamente');
    }


    // Cambiar estado del pedido (admin o repartidor)
    public function cambiarEstado(Request $request, Pedido $pedido)
    {
        $user = Auth::user();
        $nuevoEstado = $request->estado;

        if ($user->esCliente()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if ($user->esRepartidor() && !in_array($nuevoEstado, ['en reparto', 'entregado'])) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $pedido->estado = $nuevoEstado;
        $pedido->save();

        if ($nuevoEstado === 'preparación') {
            Albaran::create([
                'pedido_id' => $pedido->id,
                'fecha' => now(),
                'archivoPDF' => null
            ]);
        }

        if ($nuevoEstado === 'entregado') {
            Factura::create([
                'pedido_id' => $pedido->id,
                'fecha' => now(),
                'numero' => 'F-'.$pedido->id.'-'.time(),
                'total' => $pedido->total,
                'archivoPDF' => null
            ]);
        }

        return back();
    }

    // Mostrar albarán y factura según rol
    public function verDocumentos(Pedido $pedido)
    {
        $user = Auth::user();

        if ($user->esCliente() && $pedido->usuario_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if ($user->esRepartidor() && $pedido->repartidor_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $albaran = Albaran::where('pedido_id', $pedido->id)->first();
        $factura = Factura::where('pedido_id', $pedido->id)->first();

        return response()->json([
            'albaran' => $albaran,
            'factura' => $factura
        ]);
    }

    public function destroy(Pedido $pedido)
    {
        $pedido->delete();
        return redirect()->back()->with('success', 'Pedido eliminado correctamente');
    }

    public function marcarProductos(Request $request, Pedido $pedido)
{
    $user = Auth::user();

    // Solo repartidor asignado puede marcar
    if (!$user->esRepartidor() || $pedido->repartidor_id !== $user->id) {
        return response()->json(['error' => 'No autorizado'], 403);
    }

    $productosMarcados = $request->input('productos', []);

    // Actualizar pivote para cada producto
    foreach ($pedido->productos as $producto) {
        $pedido->productos()->updateExistingPivot($producto->id, [
            'preparado' => in_array($producto->id, $productosMarcados)
        ]);
    }

    // Cambiar estado del pedido según si hay productos preparados
    if (!empty($productosMarcados)) {
        // Usa un valor permitido en tu columna `estado`
        $pedido->estado = 'preparación'; // <- este valor debe existir en tu ENUM o VARCHAR
    } else {
        $pedido->estado = 'recibido';
    }

    $pedido->save();

    return redirect()->back();
}


}
