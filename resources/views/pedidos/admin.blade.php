@extends('layouts.app')

@section('content')

<div class="container py-4">

    {{-- Card de resumen --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h2 class="h5">Panel de administración</h2>
            <p>Total pedidos: {{ $pedidos->count() }}</p>
        </div>
    </div>

    {{-- Tabla de pedidos --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h3 class="h5 mb-3">Todos los pedidos</h3>

            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Repartidor</th>
                        <th>Estado</th>
                        <th>Seguimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedidos as $pedido)
                        <tr>
                            <td>#{{ $pedido->id }}</td>
                            <td>{{ $pedido->cliente->name ?? 'Sin cliente' }}</td>
                            <td>{{ $pedido->repartidor->name ?? 'Sin asignar' }}</td>
                            <td>{{ $pedido->estado }}</td>
                            <td><x-estado-pedido :estado="$pedido->estado" /></td>
                            <td class="d-flex gap-2">
                                {{-- Botón Ver Pedido --}}
                                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#detalle-{{ $pedido->id }}" aria-expanded="false" aria-controls="detalle-{{ $pedido->id }}">
                                    Ver Pedido
                                </button>

                                {{-- Botón Eliminar --}}
                                <form action="{{ route('pedidos.destroy', $pedido->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>

                        {{-- Fila de detalles --}}
                        <tr class="collapse" id="detalle-{{ $pedido->id }}">
                            <td colspan="6">
                                <div class="p-3 bg-light rounded">
                                    @foreach ($categorias as $categoria)
                                        @php
                                            $productosCategoria = $pedido->productos->where('categoria_id', $categoria->id);
                                        @endphp
                                        @if($productosCategoria->isNotEmpty())
                                            <h6>{{ $categoria->nombre }}</h6>
                                            <ul class="mb-2">
                                                @foreach ($productosCategoria as $producto)
                                                    <li>{{ $producto->nombre }} - Cantidad: {{ $producto->pivot->cantidad }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Bootstrap JS para collapse --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection
