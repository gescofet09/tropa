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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

class PedidoController extends Controller
{
    private const IGIC_GENERAL = 0.07;

    public function index(Request $request)
    {
        $user = Auth::user();

        // ADMIN
        if ($user->esAdmin()) {
            $estadosPedido = ['recibido', 'preparacion', 'reparto', 'entregado'];
            $busquedaPedido = trim((string) $request->query('cliente', ''));
            $estadoPedido = $request->query('estado');

            $pedidosQuery = Pedido::with('cliente', 'repartidor', 'productos.categoria', 'factura', 'albaran')
                ->latest();

            if ($busquedaPedido !== '') {
                $pedidosQuery->whereHas('cliente', function ($query) use ($busquedaPedido) {
                    $query->where('name', 'like', '%' . $busquedaPedido . '%');
                });
            }

            if (in_array($estadoPedido, $estadosPedido, true)) {
                $pedidosQuery->where('estado', $estadoPedido);
            }

            $pedidos = $pedidosQuery->get();

            $this->sincronizarTotalesConIgic($pedidos);
            $this->sincronizarDocumentos($pedidos);

            $categorias = Categoria::withCount('productos')
                ->orderBy('nombre')
                ->get();

            $productos = Producto::with('categoria')
                ->orderBy('nombre')
                ->get();

            $usuarios = User::with('zona')
                ->orderByRaw("
                    CASE
                        WHEN rol = 'admin' THEN 1
                        WHEN rol = 'repartidor' THEN 2
                        ELSE 3
                    END
                ")
                ->orderBy('name')
                ->get();

            $zonas = Zona::orderBy('nombre')->get();

            $stats = [
                'usuarios' => $usuarios->count(),
                'productos' => $productos->count(),
                'pedidos' => $pedidos->count(),
                'stock_bajo' => $productos->where('stock', '<=', 5)->count(),
                'ventas_totales' => $pedidos->sum('total'),
            ];

            $filtrosPedidos = [
                'cliente' => $busquedaPedido,
                'estado' => in_array($estadoPedido, $estadosPedido, true) ? $estadoPedido : '',
            ];

            return view('pedidos.admin', compact('pedidos', 'categorias', 'productos', 'usuarios', 'zonas', 'stats', 'filtrosPedidos', 'estadosPedido'));
        }

        // REPARTIDOR
        if ($user->esRepartidor()) {
            $pedidos = Pedido::with('cliente', 'productos', 'factura', 'albaran')
                ->whereHas('cliente', function ($query) use ($user) {
                    $query->where('zona_id', $user->zona_id);
                })
                ->get();

            $this->sincronizarTotalesConIgic($pedidos);
            $this->sincronizarDocumentos($pedidos);

            return view('pedidos.repartidor', compact('pedidos'));
        }

        // CLIENTE
        if ($user->esCliente()) {
            $pedidos = Pedido::with('productos', 'factura')
                ->where('usuario_id', $user->id)
                ->get();

            $this->sincronizarTotalesConIgic($pedidos);

            $categorias = Categoria::query()
                ->with(['productos' => function ($query) {
                    $query->orderBy('nombre');
                }])
                ->orderBy('nombre')
                ->get();

            return view('pedidos.cliente', compact('pedidos', 'categorias'));
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
        $repartidor = User::where('rol', 'repartidor')
            ->where('zona_id', $user->zona_id)
            ->first();

        if (!$repartidor) {
            return back()->with('error', 'No hay repartidores disponibles en tu zona');
        }

        // Crear pedido
        $pedido = Pedido::create([
            'usuario_id' => $user->id,
            'repartidor_id' => $repartidor->id,
            'estado' => 'recibido',
            'total' => 0
        ]);

        $baseImponible = 0;

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

            $baseImponible += $producto->precio * $cantidad;

            // Reducir stock
            $producto->stock -= $cantidad;
            $producto->save();
        }

        $pedido->total = $this->calcularTotalConIgic($baseImponible);
        $pedido->save();

        return back()->with('success', 'Pedido creado');
    }


    //* marcar productos preparados (repartidor)
    public function marcarProductos(Request $request, Pedido $pedido)
    {
        $user = Auth::user();

        $pedido->load('cliente');

        if (!$user->esRepartidor() || $pedido->cliente->zona_id !== $user->zona_id) {
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

        $albaran = null;

        if (!empty($productosMarcados)) {
            $pedido->unsetRelation('productos');
            $pedido->load('productos.categoria');
            $albaran = $this->asegurarAlbaran($pedido, true);
        }

        return response()->json([
            'success' => true,
            'estado' => $pedido->estado,
            'estado_html' => view('components.estado-pedido', ['estado' => $pedido->estado])->render(),
            'albaran' => $albaran,
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

        // REPARTIDOR solo puede cambiar a reparto o entregado, y debe ser de la misma zona
        $pedido->load('cliente');
        if ($user->esRepartidor() && (!in_array($nuevoEstado, ['reparto', 'entregado']) || $pedido->cliente->zona_id !== $user->zona_id)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Actualizar estado del pedido
        $pedido->total = $this->calcularTotalConIgic($this->calcularBasePedido($pedido));
        $pedido->estado = $nuevoEstado;
        $pedido->save();

        //* Albarán
        if (\Illuminate\Support\Str::slug((string) $nuevoEstado, '') === 'preparacion') {
            $this->asegurarAlbaran($pedido);
        }

        //* factura
        if ($nuevoEstado === 'entregado') {
            $this->asegurarFactura($pedido);
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

        $pedido->load('cliente');
        if ($user->esRepartidor() && $pedido->cliente->zona_id !== $user->zona_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $this->sincronizarTotalesConIgic(collect([$pedido]));
        $this->sincronizarDocumentos(collect([$pedido]));

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

        if (!$user->esCliente()) {
            return back()->with('error', 'No autorizado');
        }

        $repartidor = User::where('rol', 'repartidor')
            ->where('zona_id', $user->zona_id)
            ->first();

        if (!$repartidor) {
            return back()->with('error', 'No hay repartidores disponibles en tu zona');
        }

        $nuevoPedido = Pedido::create([
            'usuario_id' => $user->id,
            'repartidor_id' => $repartidor->id,
            'estado' => 'recibido',
            'total' => 0
        ]);

        $baseImponible = 0;
        $stockInsuficiente = false;

        foreach ($pedido->productos as $producto) {

            $cantidadOriginal = $producto->pivot->cantidad;

            // Si no queda stock
            if ($producto->stock <= 0) {
                $stockInsuficiente = true;
                continue;
            }

            // Ajustar cantidad al stock disponible
            $cantidadFinal = min($cantidadOriginal, $producto->stock);

            // Si no hay suficiente
            if ($cantidadFinal < $cantidadOriginal) {
                $stockInsuficiente = true;
            }

            $nuevoPedido->productos()->attach($producto->id, [
                'cantidad' => $cantidadFinal,
                'precio_unitario' => $producto->pivot->precio_unitario,
                'preparado' => false
            ]);

            // DESCONTAR STOCK
            $producto->stock -= $cantidadFinal;
            $producto->save();

            $baseImponible += $producto->pivot->precio_unitario * $cantidadFinal;
        }

        $nuevoPedido->total = $this->calcularTotalConIgic($baseImponible);
        $nuevoPedido->save();

        if ($stockInsuficiente) {
            return back()->with(
                'success',
                'Pedido realizado parcialmente. Algunons productos tenían stock insuficiente.'
            );
        }

        return back()->with('success', 'Pedido repetido correctamente');
    }



    public function storeUsuario(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:usuarios,email'],
            'rol' => ['required', Rule::in(['cliente', 'repartidor', 'admin'])],
            'zona_id' => ['nullable', 'exists:zonas,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'rol' => $data['rol'],
            'zona_id' => $data['zona_id'] ?? null,
            'password' => Hash::make(Str::random(32)),
        ]);

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Usuario creado y correo enviado para establecer contraseña');
        }

        return back()->with('error', 'No se pudo enviar el correo de recuperación');
    }  
    public function updateUsuario(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('usuarios', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'rol' => ['required', Rule::in(['cliente', 'repartidor', 'admin'])],
            'zona_id' => ['nullable', 'exists:zonas,id'],
        ]);

        if (Auth::id() === $user->id && $data['rol'] !== 'admin') {
            return back()->with('error', 'No puedes quitarte el rol de administrador.');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return back()->with('success', 'Usuario actualizado correctamente');
    }

    public function destroyUsuario(User $user)
    {
        $this->authorizeAdmin();

        if (Auth::id() === $user->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        if ($user->pedidos()->exists()) {
            return back()->with('error', 'No puedes eliminar un usuario con pedidos asociados.');
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente');
    }

    public function storeProducto(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'precio' => ['required', 'numeric', 'min:0.01'],
            'stock' => ['required', 'integer', 'min:1'],
            'unidad' => ['required', 'string', 'max:30'],
        ]);

        Producto::create($data);

        return back()->with('success', 'Producto creado correctamente');
    }

    public function updateProducto(Request $request, Producto $producto)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'unidad' => ['required', 'string', 'max:30'],
        ]);

        $producto->update($data);

        return back()->with('success', 'Producto actualizado correctamente');
    }

    public function updateStock(Request $request, Producto $producto)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $producto->update($data);

        return back()->with('success', 'Stock actualizado correctamente');
    }

    public function destroyProducto(Producto $producto)
    {
        $this->authorizeAdmin();

        if ($producto->pedidos()->exists()) {
            return back()->with('error', 'No puedes eliminar un producto que ya forma parte de pedidos.');
        }

        $producto->delete();

        return back()->with('success', 'Producto eliminado correctamente');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->esAdmin(), 403);
    }

    private function sincronizarDocumentos($pedidos): void
    {
        foreach ($pedidos as $pedido) {
            $estado = \Illuminate\Support\Str::slug((string) $pedido->estado, '');

            if (in_array($estado, ['preparacion', 'reparto', 'entregado'], true)) {
                $this->asegurarAlbaran($pedido, true);
            }

            if ($estado === 'entregado') {
                $this->asegurarFactura($pedido);
            }
        }

        if (method_exists($pedidos, 'load')) {
            $pedidos->load(['albaran', 'factura']);
        }
    }

    private function sincronizarTotalesConIgic($pedidos): void
    {
        foreach ($pedidos as $pedido) {
            $totalConIgic = $this->calcularTotalConIgic($this->calcularBasePedido($pedido));

            if (round((float) $pedido->total, 2) !== $totalConIgic) {
                $pedido->update(['total' => $totalConIgic]);
            }
        }
    }

    private function asegurarAlbaran(Pedido $pedido, bool $regenerar = false): Albaran
    {
        $pedido->loadMissing(['cliente', 'repartidor', 'productos.categoria']);

        $albaran = Albaran::firstOrCreate(
            ['pedido_id' => $pedido->id],
            ['fecha' => now(), 'archivoPDF' => null]
        );

        if ($regenerar || !$this->documentoExiste($albaran->archivoPDF)) {
            $fileName = 'albaran_pedido_'.$pedido->id.'.pdf';
            $relativePath = 'albarans/'.$fileName;

            Storage::disk('public')->makeDirectory('albarans');

            Pdf::loadView('albarans.pdf', compact('pedido', 'albaran'))
                ->setPaper('a4')
                ->save(storage_path('app/public/'.$relativePath));

            $albaran->update(['archivoPDF' => 'storage/'.$relativePath]);
        }

        return $albaran;
    }

    private function asegurarFactura(Pedido $pedido): Factura
    {
        $pedido->loadMissing(['cliente', 'repartidor', 'productos.categoria']);
        $importes = $this->calcularImportesFactura($pedido);

        $factura = Factura::firstOrCreate(
            ['pedido_id' => $pedido->id],
            [
                'fecha' => now(),
                'numero' => 'F-'.now()->format('Y').'-'.str_pad((string) $pedido->id, 5, '0', STR_PAD_LEFT),
                'total' => $importes['total'],
                'archivoPDF' => null,
            ]
        );

        $totalHaCambiado = round((float) $factura->total, 2) !== $importes['total'];

        if ($totalHaCambiado) {
            $factura->update(['total' => $importes['total']]);
            $factura->refresh();
        }

        if ($totalHaCambiado || !$this->documentoExiste($factura->archivoPDF)) {
            $fileName = 'factura_pedido_'.$pedido->id.'.pdf';
            $relativePath = 'facturas/'.$fileName;

            Storage::disk('public')->makeDirectory('facturas');

            Pdf::loadView('facturas.pdf', compact('pedido', 'factura'))
                ->setPaper('a4')
                ->save(storage_path('app/public/'.$relativePath));

            $factura->update(['archivoPDF' => 'storage/'.$relativePath]);
        }

        return $factura;
    }

    private function calcularImportesFactura(Pedido $pedido): array
    {
        $total = round((float) $pedido->total, 2);
        $base = round($total / (1 + self::IGIC_GENERAL), 2);
        $igic = round($total - $base, 2);

        return [
            'base' => $base,
            'igic_tipo' => self::IGIC_GENERAL,
            'igic' => $igic,
            'total' => $total,
        ];
    }

    private function calcularTotalConIgic(float $baseImponible): float
    {
        return round($baseImponible * (1 + self::IGIC_GENERAL), 2);
    }

    private function calcularBasePedido(Pedido $pedido): float
    {
        $pedido->loadMissing('productos');

        return round($pedido->productos->sum(function ($producto) {
            return (float) $producto->pivot->precio_unitario * (int) $producto->pivot->cantidad;
        }), 2);
    }

    private function documentoExiste(?string $publicPath): bool
    {
        if (!$publicPath) {
            return false;
        }

        $relativePath = str_replace('storage/', '', $publicPath);

        return Storage::disk('public')->exists($relativePath);
    }
    public function resetPassword(User $user)
    {
        $this->authorizeAdmin();

        $status = Password::sendResetLink([
            'email' => $user->email
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Correo de recuperación enviado.');
        }

        return back()->with('error', 'No se pudo enviar el correo.');
    }

}
