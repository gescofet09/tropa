@extends('layouts.app')

@section('content')

<style>
.detalle-productos-cliente.collapsing {
    transition: height 0s ease;
}
</style>

<div class="container py-4">

    {{-- Card Productos --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h2 class="h5 mb-3">Productos</h2>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="d-flex flex-column flex-md-row gap-2 align-items-md-end mb-3">
                <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#formulario-pedido" aria-expanded="{{ !empty($busqueda) ? 'true' : 'false' }}" aria-controls="formulario-pedido">
                    Crear nuevo pedido
                </button>

                <form id="form-busqueda-productos" action="{{ route('pedidos') }}" method="GET" class="d-flex flex-column flex-md-row gap-2 flex-grow-1">
                    <input
                        type="text"
                        id="buscar-producto"
                        name="buscar"
                        class="form-control"
                        value="{{ $busqueda ?? '' }}"
                        placeholder="Escribe el nombre del producto"
                    >
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    @if(!empty($busqueda))
                        <a href="{{ route('pedidos') }}" class="btn btn-secondary">Limpiar</a>
                    @endif
                </form>
            </div>

            {{-- Formulario de nuevo pedido --}}
            <div class="collapse {{ !empty($busqueda) ? 'show' : '' }}" id="formulario-pedido">
                <div class="card card-body mb-3">
                    <form action="{{ route('pedidos.store') }}" method="POST">
                        @csrf

                        @if(!empty($busqueda))
                            <p class="text-muted small">
                                Resultados para: <strong>{{ $busqueda }}</strong>
                            </p>
                        @endif

                        @if($categorias->isEmpty())
                            <div class="alert alert-warning mb-0">
                                No se encontraron productos con esa búsqueda.
                            </div>
                        @endif

                        @foreach($categorias as $categoria)
                            <div class="mb-2">
                                <button class="btn btn-outline-secondary w-100 text-start mb-1" type="button" data-bs-toggle="collapse" data-bs-target="#categoria-{{ $categoria->id }}" aria-expanded="{{ !empty($busqueda) ? 'true' : 'false' }}">
                                    {{ $categoria->nombre }}
                                </button>
                                <div class="collapse ms-3 {{ !empty($busqueda) ? 'show' : '' }}" id="categoria-{{ $categoria->id }}">
                                    @foreach($categoria->productos as $producto)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="productos[{{ $producto->id }}][id]" value="{{ $producto->id }}" id="producto-{{ $producto->id }}">
                                            <label class="form-check-label" for="producto-{{ $producto->id }}">
                                                <strong>{{ $producto->nombre }}</strong> - {{ $producto->precio }}€/{{ $producto->unidad }} - Stock: {{ $producto->stock }}
                                            </label>
                                            <input
                                                type="number"
                                                class="form-control form-control-sm mt-1 js-cantidad-producto"
                                                name="productos[{{ $producto->id }}][cantidad]"
                                                min="1"
                                                max="{{ $producto->stock }}"
                                                placeholder="Cantidad"
                                                style="width:100px;"
                                                data-checkbox-id="producto-{{ $producto->id }}"
                                            >
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-success mt-2">Realizar pedido</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Mis pedidos --}}
    <div class="card">
        <div class="card-body">
            <h2 class="h5 mb-3">Mis pedidos</h2>

            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Productos</th>
                        <th>Seguimiento</th>
                        <th>Documentos</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedidos as $pedido)
                        <tr data-pedido-id="{{ $pedido->id }}">
                            <td>#{{ $pedido->id }}</td>

                            {{-- Botón Ver Productos --}}
                            <td>
                                <button class="btn btn-primary btn-sm mb-1" type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#productos-{{ $pedido->id }}"
                                        aria-expanded="false"
                                        aria-controls="productos-{{ $pedido->id }}">
                                    Ver Productos
                                </button>

                                <div class="collapse mt-2 detalle-productos-cliente" id="productos-{{ $pedido->id }}">
                                    @foreach($pedido->productos as $prod)
                                        <div class="mb-1">
                                            <strong>{{ $prod->nombre }}</strong>
                                            <span class="text-muted small"> - Cantidad {{ $prod->pivot->cantidad }}</span>
                                        </div>
                                    @endforeach

                                    {{-- Repetir Pedido completo --}}
                                    <form action="{{ route('pedidos.repetir', $pedido->id) }}" method="POST" class="mt-2">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm w-100">Repetir pedido</button>
                                    </form>
                                </div>
                            </td>

                            {{-- Timeline --}}
                            <td class="estado-pedido">
                                <x-estado-pedido :estado="$pedido->estado" />
                            </td>

                            {{-- Factura --}}
                            <td class="text-center">
                                @if($pedido->factura && $pedido->factura->archivoPDF)
                                    <a href="{{ asset($pedido->factura->archivoPDF) }}" target="_blank" class="btn btn-sm btn-primary" title="Ver Factura">
                                        <i class="bi bi-file-earmark-pdf-fill"></i>
                                    </a>
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{-- Polling automático cada 5 segundos para actualizar estado del pedido --}}
<script>
document.querySelectorAll('.js-cantidad-producto').forEach(input => {
    const checkbox = document.getElementById(input.dataset.checkboxId);

    if (!checkbox) {
        return;
    }

    input.addEventListener('input', () => {
        const cantidad = Number(input.value);
        checkbox.checked = Number.isFinite(cantidad) && cantidad > 0;
    });
});

setInterval(() => {
    document.querySelectorAll('tr[data-pedido-id]').forEach(row => {
        const pedidoId = row.dataset.pedidoId;

        fetch(`/pedidos/${pedidoId}/documentos`)
            .then(res => res.json())
            .then(data => {
                if(data.estado_html){
                    row.querySelector('.estado-pedido').innerHTML = data.estado_html;
                }
            });
    });
}, 5000);
</script>

@endsection
