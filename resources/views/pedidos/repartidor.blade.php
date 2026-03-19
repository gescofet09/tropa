@extends('layouts.app')

@section('content')

<div class="space-y-4">
    <div>
        <h2 class="text-2xl font-semibold text-slate-900">Pedidos asignados</h2>
        <p class="mt-1 text-sm text-slate-500">Marca productos preparados y avanza el pedido cuando corresponda.</p>
    </div>

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

        <article class="panel" data-pedido-id="{{ $pedido->id }}" x-data="{ open: false }" @keydown.escape.window="open = false">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Pedido #{{ $pedido->id }}</h3>
                        <p class="text-sm text-slate-500">Cliente: {{ $pedido->cliente->name }}</p>
                    </div>

                    <div class="estado-pedido flex flex-wrap gap-2">
                        @foreach($estadosTimeline as $estado)
                            @php
                                $isActive = array_search($estadoPedido, $estadosTimeline) >= array_search($estado, $estadosTimeline);
                                $color = match($estado) {
                                    'recibido' => 'bg-slate-500 text-white',
                                    'preparacion' => 'bg-amber-400 text-slate-900',
                                    'reparto' => 'bg-sky-400 text-slate-900',
                                    'entregado' => 'bg-emerald-500 text-white',
                                };
                            @endphp
                            <div
                                class="min-w-[110px] rounded-full px-4 py-2 text-center text-xs font-medium sm:text-sm {{ $isActive ? $color : 'bg-slate-200 text-slate-500' }}"
                                data-estado="{{ $estado }}"
                                style="{{ $estadoPedido === $estado ? 'font-weight: bold;' : ($isActive ? '' : 'opacity: 0.6;') }}"
                            >
                                {{ $labelsTimeline[$estado] }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <button class="btn-primary self-start" type="button" @click="open = true">Ver pedido</button>
            </div>

            <template x-teleport="body">
                <div x-show="open" x-cloak>
                    <div class="modal-overlay" @click="open = false"></div>

                    <div class="modal-panel max-w-3xl max-h-[85vh] overflow-y-auto" @click.stop>
                        <div class="modal-header">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Pedido #{{ $pedido->id }}</h3>
                                <p class="text-sm text-slate-500">Cliente: {{ $pedido->cliente->name }}</p>
                            </div>

                            <button class="btn-secondary" type="button" @click="open = false">Cerrar</button>
                        </div>

                        <div class="space-y-4">
                            <form class="form-checklist space-y-2" id="form-pedido-{{ $pedido->id }}">
                                @csrf
                                @foreach($pedido->productos as $producto)
                                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        @if(in_array($estadoPedido, ['recibido','preparacion']))
                                            <input
                                                type="checkbox"
                                                class="checkbox-producto h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                                                data-pedido-id="{{ $pedido->id }}"
                                                value="{{ $producto->id }}"
                                                {{ $producto->pivot->preparado ? 'checked' : '' }}
                                            >
                                        @endif

                                        <span class="text-sm text-slate-700">
                                            <strong class="font-semibold">{{ $producto->nombre }}</strong> - Cantidad: {{ $producto->pivot->cantidad }}
                                        </span>
                                    </label>
                                @endforeach
                            </form>

                            <div class="accion-reparto {{ $estadoPedido === 'preparacion' ? '' : 'hidden' }}">
                                <form action="{{ route('pedidos.cambiarEstado', $pedido->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="estado" value="reparto">
                                    <button class="btn-primary" type="submit">Marcar como Reparto</button>
                                </form>
                            </div>

                            @if($estadoPedido === 'reparto')
                                <form action="{{ route('pedidos.cambiarEstado', $pedido->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="estado" value="entregado">
                                    <button class="btn-success" type="submit">Marcar como Entregado</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </template>
        </article>
    @endforeach
</div>

<script>
function actualizarTimelineRepartidor(timeline, estadoActual) {
    if (!timeline) {
        return;
    }

    const estados = ['recibido', 'preparacion', 'reparto', 'entregado'];
    const colores = {
        recibido: ['bg-slate-500', 'text-white'],
        preparacion: ['bg-amber-400', 'text-slate-900'],
        reparto: ['bg-sky-400', 'text-slate-900'],
        entregado: ['bg-emerald-500', 'text-white'],
    };

    const estadoNormalizado = (estadoActual || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    const indiceActual = Math.max(estados.indexOf(estadoNormalizado), 0);

    timeline.querySelectorAll('[data-estado]').forEach(step => {
        const estadoStep = step.dataset.estado;
        const indiceStep = estados.indexOf(estadoStep);
        const esPasado = indiceStep < indiceActual;
        const esActual = estadoStep === estadoNormalizado;

        step.className = 'min-w-[110px] rounded-full px-4 py-2 text-center text-xs font-medium sm:text-sm';

        if (esPasado || esActual) {
            step.classList.add(...(colores[estadoStep] || []));
        } else {
            step.classList.add('bg-slate-200', 'text-slate-500');
        }

        step.style.fontWeight = esActual ? 'bold' : '';
        step.style.opacity = (!esPasado && !esActual) ? '0.6' : '';
    });
}

document.addEventListener('change', event => {
    if (!event.target.classList.contains('checkbox-producto')) {
        return;
    }

    const checkbox = event.target;
    const pedidoId = checkbox.dataset.pedidoId;
    const form = document.getElementById(`form-pedido-${pedidoId}`);

    if (!form) {
        return;
    }

    const tarjetaPedido = document.querySelector(`article[data-pedido-id="${pedidoId}"]`);
    const contenedorModal = form.closest('.modal-panel');
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
        if (data.success) {
            const timeline = tarjetaPedido?.querySelector('.estado-pedido');
            const accionReparto = contenedorModal?.querySelector('.accion-reparto');
            const estado = (data.estado || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '');

            actualizarTimelineRepartidor(timeline, data.estado);

            if (accionReparto) {
                accionReparto.classList.toggle('hidden', estado !== 'preparacion');
            }
        }
    });
});
</script>

@endsection
