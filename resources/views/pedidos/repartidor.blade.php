@extends('layouts.app')

@section('content')

<style>
.detalle-pedido.collapsing {
    transition: height 0s ease;
}
</style>

<div class="container py-4">
    <h2 class="mb-4">Pedidos asignados</h2>

    @foreach($pedidos as $pedido)
        @php
            $estadoPedido = Str::slug($pedido->estado, '');
            $estadosTimeline = ['recibido','preparacion','reparto','entregado'];
            $labelsTimeline = [
                'recibido' => 'Recibido',
                'preparacion' => 'Preparación',
                'reparto' => 'Reparto',
                'entregado' => 'Entregado',
            ];
        @endphp

        <div class="card mb-3 shadow-sm p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5>Pedido #{{ $pedido->id }} - Cliente: {{ $pedido->cliente->name }}</h5>

                {{-- Botón Ver Pedido --}}
                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#detalle-{{ $pedido->id }}" aria-expanded="false">
                    Ver Pedido
                </button>
            </div>

            {{-- Timeline de estados --}}
            <div class="d-flex mb-3 estado-pedido">
                @foreach($estadosTimeline as $estado)
                    @php
                        $isActive = array_search($estadoPedido, $estadosTimeline) >= array_search($estado, $estadosTimeline);
                        $color = match($estado) {
                            'recibido' => 'bg-secondary text-white',
                            'preparacion' => 'bg-warning text-dark',
                            'reparto' => 'bg-info text-dark',
                            'entregado' => 'bg-success text-white',
                        };
                    @endphp
                    <div
                        class="flex-fill text-center p-1 mx-1 rounded {{ $isActive ? $color : 'bg-light text-muted' }}"
                        data-estado="{{ $estado }}"
                    >
                        {{ $labelsTimeline[$estado] }}
                    </div>
                @endforeach
            </div>

            {{-- Contenido colapsable --}}
            <div class="collapse detalle-pedido" id="detalle-{{ $pedido->id }}">
                <form class="form-checklist" id="form-pedido-{{ $pedido->id }}">
                    @csrf
                    @foreach($pedido->productos as $producto)
                        <div class="d-flex align-items-center mb-1">
                            {{-- Si el estado es recibido o preparación, mostramos checkbox --}}
                            @if(in_array($estadoPedido, ['recibido','preparacion']))
                                <input type="checkbox"
                                       class="form-check-input checkbox-producto me-2"
                                       data-pedido-id="{{ $pedido->id }}"
                                       value="{{ $producto->id }}"
                                       {{ $producto->pivot->preparado ? 'checked' : '' }}>
                            @endif

                            {{-- Nombre y cantidad del producto --}}
                            <span><strong>{{ $producto->nombre }} </strong>- Cantidad: {{ $producto->pivot->cantidad }}</span>
                        </div>
                    @endforeach
                </form>

                {{-- Botón En reparto --}}
                <div class="accion-reparto {{ $estadoPedido === 'preparacion' ? '' : 'd-none' }}">
                    <form action="{{ route('pedidos.cambiarEstado', $pedido->id) }}" method="POST" class="mt-2">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado" value="reparto">
                        <button class="btn btn-primary btn-sm">Marcar como Reparto</button>
                    </form>
                </div>

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
        </div>
    @endforeach
</div>

{{-- Script para actualizar productos preparados sin recargar --}}
<script>
function actualizarTimelineRepartidor(timeline, estadoActual) {
    if (!timeline) {
        return;
    }

    const estados = ['recibido', 'preparacion', 'reparto', 'entregado'];
    const colores = {
        recibido: ['bg-secondary', 'text-white'],
        preparacion: ['bg-warning', 'text-dark'],
        reparto: ['bg-info', 'text-dark'],
        entregado: ['bg-success', 'text-white'],
    };

    const estadoNormalizado = (estadoActual || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    const indiceActual = Math.max(estados.indexOf(estadoNormalizado), 0);

    timeline.querySelectorAll('[data-estado]').forEach(step => {
        const estadoStep = step.dataset.estado;
        const indiceStep = estados.indexOf(estadoStep);
        const esPasado = indiceStep < indiceActual;
        const esActual = estadoStep === estadoNormalizado;
        const clasesColor = colores[estadoStep] || [];

        step.className = 'flex-fill text-center p-1 mx-1 rounded';

        if (esPasado || esActual) {
            step.classList.add(...clasesColor);
        } else {
            step.classList.add('bg-light', 'text-muted');
        }

        step.style.border = '';
        step.style.fontWeight = esActual ? 'bold' : '';
        step.style.opacity = (!esPasado && !esActual) ? '0.6' : '';
    });
}

document.querySelectorAll('.checkbox-producto').forEach(function(checkbox){
    checkbox.addEventListener('change', function(){
        const pedidoId = this.dataset.pedidoId;
        const form = document.getElementById('form-pedido-' + pedidoId);
        const tarjetaPedido = this.closest('.card');
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
                const timeline = tarjetaPedido.querySelector('.estado-pedido');
                const accionReparto = tarjetaPedido.querySelector('.accion-reparto');

                actualizarTimelineRepartidor(timeline, data.estado);

                if (accionReparto) {
                    const estado = (data.estado || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    accionReparto.classList.toggle('d-none', estado !== 'preparacion');
                }
            }
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection
