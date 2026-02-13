@extends('layouts.app')

@section('content')

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

            {{-- Botón Crear Pedido --}}
            <button class="btn btn-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#formulario-pedido" aria-expanded="false" aria-controls="formulario-pedido">
                Crear nuevo pedido
            </button>

            {{-- Formulario de nuevo pedido --}}
            <div class="collapse" id="formulario-pedido">
                <div class="card card-body mb-3">
                    <form action="{{ route('pedidos.store') }}" method="POST">
                        @csrf

                        @foreach($categorias as $categoria)
                            <div class="mb-2">
                                <button class="btn btn-outline-secondary w-100 text-start mb-1" type="button" data-bs-toggle="collapse" data-bs-target="#categoria-{{ $categoria->id }}">
                                    {{ $categoria->nombre }}
                                </button>
                                <div class="collapse ms-3" id="categoria-{{ $categoria->id }}">
                                    @foreach($categoria->productos as $producto)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="productos[{{ $producto->id }}][id]" value="{{ $producto->id }}" id="producto-{{ $producto->id }}">
                                            <label class="form-check-label" for="producto-{{ $producto->id }}">
                                                {{ $producto->nombre }} - {{ $producto->precio }}€/{{ $producto->unidad }} - Stock: {{ $producto->stock }}
                                            </label>
                                            <input type="number" class="form-control form-control-sm mt-1" name="productos[{{ $producto->id }}][cantidad]" min="1" max="{{ $producto->stock }}" placeholder="Cantidad" style="width:100px;">
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
                        <th>Cantidad</th>
                        <!-- <th>Estado</th> -->
                        <th>Seguimiento</th>
                        <th>Documentos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedidos as $pedido)
                        <tr>
                            <td>#{{ $pedido->id }}</td>
                            <td>
                                @foreach($pedido->productos as $prod)
                                    {{ $prod->nombre }}<br>
                                @endforeach
                            </td>
                            <td>
                                @foreach($pedido->productos as $prod)
                                    {{ $prod->pivot->cantidad }}<br>
                                @endforeach
                            </td>
                            <!-- <td>{{ $pedido->estado }}</td> -->
                            <td>
                                <x-estado-pedido :estado="$pedido->estado" />
                            </td>
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

{{-- Bootstrap JS para collapse --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection
