@extends('layouts.app')

@section('content')

<div class="card">
    <h2>Pedidos asignados</h2>

    @foreach($pedidos as $pedido)
        <div class="pedido-card">
            <h3>Pedido #{{ $pedido->id }} - Cliente: {{ $pedido->cliente->name }}</h3>
            <p>Estado: <x-estado-pedido :estado="$pedido->estado" /></p>

            <form action="{{ route('pedidos.marcarProductos', $pedido->id) }}" method="POST">
                @csrf
                @foreach($pedido->productos as $producto)
                    <div>
                        <input type="checkbox" name="productos[]" value="{{ $producto->id }}"
                            onchange="this.form.submit()"
                            @if($producto->pivot->preparado) checked @endif
                        >
                        {{ $producto->nombre }} - Cantidad: {{ $producto->pivot->cantidad }}
                    </div>
                @endforeach
            </form>
        </div>
    @endforeach

</div>

@endsection
