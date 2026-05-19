@extends('layouts.app')

@section('content')
    @php
        $estadosTimeline = ['recibido', 'preparacion', 'reparto', 'entregado'];
        $labelsTimeline = [
            'recibido' => 'Recibido',
            'preparacion' => 'Preparación',
            'reparto' => 'Reparto',
            'entregado' => 'Entregado',
        ];
        $estadosPendientes = ['recibido', 'preparacion'];
        $pedidosPagina = method_exists($pedidos, 'getCollection') ? $pedidos->getCollection() : $pedidos;

        $pedidosPorCliente = $pedidosPagina
            ->groupBy(fn ($pedido) => $pedido->cliente?->id ? 'cliente-' . $pedido->cliente->id : 'sin-cliente')
            ->sortBy(function ($grupo) use ($estadosPendientes) {
                $tienePendientes = $grupo->contains(function ($pedido) use ($estadosPendientes) {
                    return in_array(\Illuminate\Support\Str::slug($pedido->estado, ''), $estadosPendientes, true);
                });

                $cliente = $grupo->first()?->cliente?->name ?? 'Sin cliente';

                return ($tienePendientes ? '0-' : '1-') . $cliente;
            });
    @endphp

    <div class="space-y-4">
        <div>
            <h2 class="text-2xl font-semibold text-slate-900">Pedidos asignados</h2>
            <p class="mt-1 text-sm text-slate-500">Agrupados por cliente. Los clientes con pedidos pendientes aparecen primero.</p>
        </div>

        <div class="space-y-4">
            @foreach ($pedidosPorCliente as $clienteKey => $pedidosCliente)
                @php
                    $cliente = $pedidosCliente->first()?->cliente;
                    $nombreCliente = $cliente?->name ?? 'Sin cliente';
                    $pendientesCliente = $pedidosCliente->filter(function ($pedido) use ($estadosPendientes) {
                        return in_array(\Illuminate\Support\Str::slug($pedido->estado, ''), $estadosPendientes, true);
                    });
                    $pedidosOrdenados = $pedidosCliente->sortBy(function ($pedido) use ($estadosPendientes) {
                        $estadoNormalizado = \Illuminate\Support\Str::slug($pedido->estado, '');
                        $prioridad = in_array($estadoNormalizado, $estadosPendientes, true) ? 0 : 1;

                        return sprintf('%d-%010d', $prioridad, 9999999999 - $pedido->id);
                    });
                @endphp

                <details class="overflow-hidden rounded-2xl border border-slate-200 bg-white" {{ $pendientesCliente->isNotEmpty() ? 'open' : '' }}>
                    <summary class="flex cursor-pointer list-none flex-col gap-3 bg-slate-50/80 px-4 py-4 marker:hidden sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $nombreCliente }}</h3>
                                <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    {{ $pedidosCliente->count() }} pedidos
                                </span>
                                @if ($pendientesCliente->isNotEmpty())
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        {{ $pendientesCliente->count() }} pendientes
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $pendientesCliente->isNotEmpty() ? 'Revisa primero los pedidos sin atender de este cliente.' : 'Todos los pedidos de este cliente están avanzados.' }}
                            </p>
                        </div>

                        <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
                            Ver pedidos
                        </span>
                    </summary>

                    <div class="divide-y divide-slate-100">
                        @foreach ($pedidosOrdenados as $pedido)
                            @php
                                $estadoPedido = \Illuminate\Support\Str::slug($pedido->estado, '');
                                $esPendiente = in_array($estadoPedido, $estadosPendientes, true);
                            @endphp

                            <article class="panel !rounded-none !border-0 !shadow-none {{ $esPendiente ? 'bg-amber-50/40' : '' }}" data-pedido-id="{{ $pedido->id }}">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="space-y-4">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h4 class="text-lg font-semibold text-slate-900">Pedido #{{ $pedido->id }}</h4>
                                            @if ($esPendiente)
                                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Pendiente</span>
                                            @endif
                                        </div>

                                        <div class="estado-pedido flex flex-wrap gap-2">
                                            @foreach ($estadosTimeline as $estado)
                                                @php
                                                    $isActive = array_search($estadoPedido, $estadosTimeline) >= array_search($estado, $estadosTimeline);
                                                    $color = match ($estado) {
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

                                    <div class="acciones-pedido flex flex-wrap gap-2 self-start">
                                        <button class="btn-primary" type="button" data-modal-open="repartidor-pedido-{{ $pedido->id }}">Ver pedido</button>
                                        @if($pedido->albaran?->archivoPDF)
                                            <a href="{{ asset($pedido->albaran->archivoPDF) }}" target="_blank" class="btn-secondary albaran-link">
                                                Albarán
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <div id="repartidor-pedido-{{ $pedido->id }}" class="hidden" data-modal>
                                    <div class="modal-overlay" data-modal-close></div>

                                    <div class="modal-panel max-h-[85vh] max-w-3xl overflow-y-auto">
                                        <div class="modal-header">
                                            <div>
                                                <h3 class="text-lg font-semibold text-slate-900">Pedido #{{ $pedido->id }}</h3>
                                                <p class="text-sm text-slate-500">Cliente: {{ $pedido->cliente->name }}</p>
                                            </div>

                                            <button class="btn-secondary" type="button" data-modal-close>Cerrar</button>
                                        </div>

                                        <div class="space-y-4">
                                            <form class="form-checklist space-y-2" id="form-pedido-{{ $pedido->id }}">
                                                @csrf
                                                @foreach ($pedido->productos as $producto)
                                                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                                        @if (in_array($estadoPedido, ['recibido', 'preparacion']))
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

                                            <div class="documentos-pedido flex flex-wrap gap-2">
                                                @if($pedido->albaran?->archivoPDF)
                                                    <a href="{{ asset($pedido->albaran->archivoPDF) }}" target="_blank" class="btn-secondary albaran-link">
                                                        Albarán
                                                    </a>
                                                @endif
                                            </div>

                                            <div class="accion-reparto {{ $estadoPedido === 'preparacion' ? '' : 'hidden' }}">
                                                <form action="{{ route('pedidos.cambiarEstado', $pedido->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="estado" value="reparto">
                                                    <button class="btn-primary" type="submit">Marcar como Reparto</button>
                                                </form>
                                            </div>

                                            @if ($estadoPedido === 'reparto')
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
                            </article>
                        @endforeach
                    </div>
                </details>
            @endforeach
        </div>

        @if (method_exists($pedidos, 'hasPages') && $pedidos->hasPages())
            <div class="pt-2">
                {{ $pedidos->links() }}
            </div>
        @endif
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
                    const documentosPedido = contenedorModal?.querySelector('.documentos-pedido');
                    const accionesPedido = tarjetaPedido?.querySelector('.acciones-pedido');
                    const estado = (data.estado || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '');

                    actualizarTimelineRepartidor(timeline, data.estado);

                    if (accionReparto) {
                        accionReparto.classList.toggle('hidden', estado !== 'preparacion');
                    }

                    if (data.albaran?.archivoPDF) {
                        const albaranUrl = `${window.location.origin}/${data.albaran.archivoPDF}`;

                        if (documentosPedido && !documentosPedido.querySelector('.albaran-link')) {
                            documentosPedido.insertAdjacentHTML('beforeend', `<a href="${albaranUrl}" target="_blank" class="btn-secondary albaran-link">Albarán</a>`);
                        }

                        if (accionesPedido && !accionesPedido.querySelector('.albaran-link')) {
                            accionesPedido.insertAdjacentHTML('beforeend', `<a href="${albaranUrl}" target="_blank" class="btn-secondary albaran-link">Albarán</a>`);
                        }
                    }
                }
            });
        });
    </script>
@endsection
