<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Albaran;
use App\Models\Factura;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PedidoController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $busqueda = trim((string) $request->query('buscar', ''));

        // ADMIN
        if ($user->esAdmin()) {
            $pedidos = Pedido::with('cliente', 'repartidor', 'productos', 'factura', 'albaran')->get();
            $categorias = Categoria::all();

            return view('pedidos.admin', compact('pedidos', 'categorias'));
        }

        // REPARTIDOR
        if ($user->esRepartidor()) {
            $pedidos = Pedido::with('cliente', 'productos', 'factura', 'albaran')
                ->where('repartidor_id', $user->id)
                ->get();

            return view('pedidos.repartidor', compact('pedidos'));
        }

        // CLIENTE
        if ($user->esCliente()) {
            $pedidos = Pedido::with('productos', 'factura')
                ->where('usuario_id', $user->id)
                ->get();

            $categorias = Categoria::query()
                ->when($busqueda !== '', function ($query) use ($busqueda) {
                    $query->whereHas('productos', function ($productoQuery) use ($busqueda) {
                        $productoQuery->where('nombre', 'like', '%'.$busqueda.'%');
                    });
                })
                ->with(['productos' => function ($query) use ($busqueda) {
                    $query->when($busqueda !== '', function ($productoQuery) use ($busqueda) {
                        $productoQuery->where('nombre', 'like', '%'.$busqueda.'%');
                    })->orderBy('nombre');
                }])
                ->orderBy('nombre')
                ->get();

            return view('pedidos.cliente', compact('pedidos', 'categorias', 'busqueda'));
        }
    }


    //* Crear pedido (cliente)

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->esCliente()) {
            return back()->with('error', 'Solo clientes pueden crear pedidos');
        }

        $productosSeleccionados = $request->input('productos', []);

        if (empty($productosSeleccionados)) {
            return back()->with('error', 'Selecciona al menos un producto');
        }

        // Buscar repartidor de la misma zona
        $repartidor = User::where('rol', 'repartidor')->first();

        if (!$repartidor) {
            return back()->with('error', 'No hay repartidores disponibles');
        }

        // Crear pedido
        $pedido = Pedido::create([
            'usuario_id' => $user->id,
            'repartidor_id' => $repartidor->id,
            'estado' => 'recibido',
            'total' => 0
        ]);

        $total = 0;

        foreach ($productosSeleccionados as $productoData) {

            if (!isset($productoData['id'])) continue;

            $producto = Producto::find($productoData['id']);
            if (!$producto) continue;

            $cantidad = max(1, (int)$productoData['cantidad']);
            $cantidad = min($cantidad, $producto->stock);

            $pedido->productos()->attach($producto->id, [
                'cantidad' => $cantidad,
                'precio_unitario' => $producto->precio,
                'preparado' => false
            ]);

            $total += $producto->precio * $cantidad;

            // Reducir stock
            $producto->stock -= $cantidad;
            $producto->save();
        }

        $pedido->total = $total;
        $pedido->save();

        return back()->with('success', 'Pedido creado');
    }


    //* marcar productos preparados (repartidor)
    public function marcarProductos(Request $request, Pedido $pedido)
    {
        $user = Auth::user();

        if (!$user->esRepartidor() || $pedido->repartidor_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $productosMarcados = $request->input('productos', []);

        foreach ($pedido->productos as $producto) {
            $pedido->productos()->updateExistingPivot($producto->id, [
                'preparado' => in_array($producto->id, $productosMarcados)
            ]);
        }

        // Si hay al menos uno marcado -> preparacion
        if (!empty($productosMarcados)) {
            $pedido->estado = 'preparacion';
        } else {
            $pedido->estado = 'recibido';
        }

        $pedido->save();

        return response()->json([
            'success' => true,
            'estado' => $pedido->estado,
            'estado_html' => view('components.estado-pedido', ['estado' => $pedido->estado])->render(),
        ]);
    }



    //? cambiar estado (repartidor/admin)
    public function cambiarEstado(Request $request, Pedido $pedido)
    {
        $user = Auth::user();
        $nuevoEstado = $request->estado;

        // CLIENTE no puede cambiar estados
        if ($user->esCliente()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // REPARTIDOR solo puede cambiar a reparto o entregado
        if ($user->esRepartidor() && !in_array($nuevoEstado, ['reparto', 'entregado'])) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Actualizar estado del pedido
        $pedido->estado = $nuevoEstado;
        $pedido->save();

        //* Albarán 
        if (\Illuminate\Support\Str::slug((string) $nuevoEstado, '') === 'preparacion') {

            // Crear registro de albarán
            $albaran = Albaran::create([
                'pedido_id' => $pedido->id,
                'fecha' => now(),
                'archivoPDF' => null
            ]);

            // Asegurarnos de que exista la carpeta
            $albaransPath = storage_path('app/public/albarans');
            if (!file_exists($albaransPath)) {
                mkdir($albaransPath, 0775, true);
            }

            // Generar PDF
            $fileName = 'albaran_'.$albaran->id.'.pdf';
            $pdf = Pdf::loadView('albarans.pdf', compact('pedido', 'albaran'));
            $pdf->save($albaransPath.'/'.$fileName);

            // Guardar ruta en la base de datos
            $albaran->archivoPDF = 'storage/albarans/'.$fileName;
            $albaran->save();
        }

        //* factura
        if ($nuevoEstado === 'entregado') {

            // Crear registro de factura
            $factura = Factura::create([
                'pedido_id' => $pedido->id,
                'fecha' => now(),
                'numero' => 'F-'.$pedido->id.'-'.time(),
                'total' => $pedido->total,
                'archivoPDF' => null
            ]);

            // Asegurarnos de que exista la carpeta
            $facturasPath = storage_path('app/public/facturas');
            if (!file_exists($facturasPath)) {
                mkdir($facturasPath, 0775, true);
            }

            // Generar PDF
            $fileName = 'factura_'.$factura->id.'.pdf';
            $pdf = Pdf::loadView('facturas.pdf', compact('pedido', 'factura'));
            $pdf->save($facturasPath.'/'.$fileName);

            // Guardar ruta en la base de datos
            $factura->archivoPDF = 'storage/facturas/'.$fileName;
            $factura->save();
        }

        return back()->with('success', 'Estado actualizado');
    }



    //todo ELIMINAR pedido (admin)
    public function destroy(Pedido $pedido)
    {
        $user = Auth::user();

        if (!$user->esAdmin()) {
            return back()->with('error', 'No autorizado');
        }

        $pedido->productos()->detach();
        $pedido->delete();

        return back()->with('success', 'Pedido eliminado');
    }


    //? ver Documentos
    public function verDocumentos(Pedido $pedido)
    {
        $user = Auth::user();

        if ($user->esCliente() && $pedido->usuario_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if ($user->esRepartidor() && $pedido->repartidor_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $pedido->load(['albaran', 'factura']);

        return response()->json([
            'estado' => $pedido->estado,
            'estado_html' => view('components.estado-pedido', ['estado' => $pedido->estado])->render(),
            'albaran' => $pedido->albaran,
            'factura' => $pedido->factura
        ]);
    }

    public function repetir(Pedido $pedido)
    {
        $user = Auth::user();
        if(!$user->esCliente()) {
            return back()->with('error', 'No autorizado');
        }

        $nuevoPedido = Pedido::create([
            'usuario_id' => $user->id,
            'repartidor_id' => $pedido->repartidor_id,
            'estado' => 'recibido',
            'total' => $pedido->total
        ]);

        foreach ($pedido->productos as $producto) {
            $nuevoPedido->productos()->attach($producto->id, [
                'cantidad' => $producto->pivot->cantidad,
                'precio_unitario' => $producto->pivot->precio_unitario,
                'preparado' => false
            ]);
        }

        return back()->with('success', 'Pedido repetido correctamente');
    }
    

}
