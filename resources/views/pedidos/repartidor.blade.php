@extends('layouts.app')

@section('content')

<div class="container py-4">
    <h2 class="mb-4">Pedidos asignados</h2>

    @foreach($pedidos as $pedido)
        @php
            // Normalizamos estado
            $estadoPedido = Str::slug($pedido->estado, '');
            $estadosTimeline = ['recibido','preparacion','reparto','entregado'];
        @endphp

        <div class="card mb-3 shadow-sm p-3">
            <h5>Pedido #{{ $pedido->id }} - Cliente: {{ $pedido->cliente->name }}</h5>

            {{-- Timeline de estados --}}
            <div class="d-flex mb-3">
                @foreach($estadosTimeline as $estado)
                    @php
                        $isActive = array_search($estadoPedido, $estadosTimeline) >= array_search($estado, $estadosTimeline);
                        $color = match($estado) {
                            'recibido' => 'bg-secondary',
                            'preparacion' => 'bg-warning text-dark',
                            'reparto' => 'bg-primary text-white',
                            'entregado' => 'bg-success text-white',
                        };
                    @endphp
                    <div class="flex-fill text-center p-1 mx-1 rounded {{ $isActive ? $color : 'bg-light' }}">
                        {{ ucfirst($estado) }}
                    </div>
                @endforeach
            </div>

            {{-- Checklist solo para recibido o preparación --}}
            @if(in_array($estadoPedido, ['recibido','preparacion']))
                <form class="form-checklist" id="form-pedido-{{ $pedido->id }}">
                    @csrf
                    @foreach($pedido->productos as $producto)
                        <div class="form-check mb-1">
                            <input type="checkbox"
                                   class="form-check-input checkbox-producto"
                                   data-pedido-id="{{ $pedido->id }}"
                                   value="{{ $producto->id }}"
                                   {{ $producto->pivot->preparado ? 'checked' : '' }}>
                            <label class="form-check-label">
                                {{ $producto->nombre }} - Cantidad: {{ $producto->pivot->cantidad }}
                            </label>
                        </div>
                    @endforeach
                </form>
            @endif

            {{-- Botón En reparto --}}
            @if($estadoPedido === 'preparacion')
                <form action="{{ route('pedidos.cambiarEstado', $pedido->id) }}" method="POST" class="mt-2">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="estado" value="reparto">
                    <button class="btn btn-primary btn-sm">Marcar como Reparto</button>
                </form>
            @endif

            {{-- Botón Entregado --}}
            @if($estadoPedido === 'reparto')
                <form action="{{ route('pedidos.cambiarEstado', $pedido->id) }}" method="POST" class="mt-2">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="estado" value="entregado">
                    <button class="btn btn-success btn-sm">Marcar como Entregado</button>
                </form>
            @endif

        </div>
    @endforeach
</div>

{{-- Script para actualizar productos preparados sin recargar --}}
<script>
document.querySelectorAll('.checkbox-producto').forEach(function(checkbox){
    checkbox.addEventListener('change', function(){
        const pedidoId = this.dataset.pedidoId;
        const form = document.getElementById('form-pedido-' + pedidoId);
        const productos = Array.from(form.querySelectorAll('.checkbox-producto:checked')).map(el => el.value);

        fetch(`/pedidos/${pedidoId}/marcar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ productos })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                location.reload(); // recargamos para actualizar el timeline
            }
        });
    });
});
</script>

@endsection
