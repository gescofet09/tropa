@extends('layouts.app')

@section('content')

<div class="space-y-6">
    <section class="panel" x-data="{ crearPedidoAbierto: {{ !empty($busqueda) ? 'true' : 'false' }} }" @keydown.escape.window="crearPedidoAbierto = false">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Productos</h2>
                <p class="text-sm text-slate-500">Busca y prepara tu pedido desde aquí.</p>
            </div>

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <button
                    class="btn-primary"
                    type="button"
                    @click="crearPedidoAbierto = true"
                    x-text="'Crear nuevo pedido'"
                ></button>

                <form id="form-busqueda-productos" action="{{ route('pedidos') }}" method="GET" class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                    <input
                        type="text"
                        id="buscar-producto"
                        name="buscar"
                        class="input-base sm:w-72"
                        value="{{ $busqueda ?? '' }}"
                        placeholder="Escribe el nombre del producto"
                    >
                    <button type="submit" class="btn-primary">Buscar</button>
                    @if(!empty($busqueda))
                        <a href="{{ route('pedidos') }}" class="btn-secondary">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="mt-4 space-y-3">
            @if(session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert-danger">{{ session('error') }}</div>
            @endif
        </div>

        <template x-teleport="body">
            <div x-show="crearPedidoAbierto" x-cloak>
                <div class="modal-overlay" @click="crearPedidoAbierto = false"></div>

                <div class="modal-panel max-h-[85vh] overflow-y-auto" @click.stop>
                    <div class="modal-header">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Crear nuevo pedido</h3>
                            <p class="text-sm text-slate-500">Selecciona productos y cantidades.</p>
                        </div>

                        <button class="btn-secondary" type="button" @click="crearPedidoAbierto = false">Cerrar</button>
                    </div>

                    <form action="{{ route('pedidos.store') }}" method="POST" class="space-y-4">
                @csrf

                        @if(!empty($busqueda))
                            <p class="text-sm text-slate-500">
                                Resultados para: <span class="font-semibold text-slate-700">{{ $busqueda }}</span>
                            </p>
                        @endif

                        @if($categorias->isEmpty())
                            <div class="alert-warning">No se encontraron productos con esa búsqueda.</div>
                        @endif

                        <div class="space-y-3">
                            @foreach($categorias as $categoria)
                                <div class="rounded-2xl border border-slate-200 bg-white" x-data="{ open: {{ !empty($busqueda) ? 'true' : 'false' }} }">
                                    <button
                                        class="flex w-full items-center justify-between rounded-2xl px-4 py-3 text-left text-sm font-semibold text-slate-700"
                                        type="button"
                                        @click="open = !open"
                                    >
                                        <span>{{ $categoria->nombre }}</span>
                                        <svg class="h-4 w-4 text-slate-400 transition" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <div x-show="open" class="space-y-3 border-t border-slate-100 px-4 py-4">
                                        @foreach($categoria->productos as $producto)
                                            <label class="block rounded-xl border border-slate-200 p-3">
                                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                    <div class="flex items-start gap-3">
                                                        <input
                                                            class="mt-1 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                                                            type="checkbox"
                                                            name="productos[{{ $producto->id }}][id]"
                                                            value="{{ $producto->id }}"
                                                            id="producto-{{ $producto->id }}"
                                                        >
                                                        <div>
                                                            <p class="font-semibold text-slate-800">{{ $producto->nombre }}</p>
                                                            <p class="text-sm text-slate-500">{{ $producto->precio }}€/{{ $producto->unidad }} · Stock: {{ $producto->stock }}</p>
                                                        </div>
                                                    </div>

                                                    <input
                                                        type="number"
                                                        class="input-base js-cantidad-producto w-full sm:w-28"
                                                        name="productos[{{ $producto->id }}][cantidad]"
                                                        min="1"
                                                        max="{{ $producto->stock }}"
                                                        placeholder="Cantidad"
                                                        data-checkbox-id="producto-{{ $producto->id }}"
                                                    >
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-success">Realizar pedido</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </section>

    <section class="panel">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Mis pedidos</h2>
                <p class="text-sm text-slate-500">Consulta productos, estado y factura.</p>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Productos</th>
                        <th class="px-4 py-3">Seguimiento</th>
                        <th class="px-4 py-3">Documentos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($pedidos as $pedido)
                        <tr data-pedido-id="{{ $pedido->id }}" class="align-top">
                            <td class="px-4 py-4 font-semibold text-slate-700">#{{ $pedido->id }}</td>
                            <td class="px-4 py-4">
                                <div x-data="{ open: false }" @keydown.escape.window="open = false" class="space-y-3">
                                    <button
                                        class="btn-primary btn-base !px-3 !py-1.5 text-xs"
                                        type="button"
                                        @click="open = true"
                                        x-text="'Ver productos'"
                                    ></button>

                                    <template x-teleport="body">
                                        <div x-show="open" x-cloak>
                                            <div class="modal-overlay" @click="open = false"></div>

                                            <div class="modal-panel max-w-2xl" @click.stop>
                                                <div class="modal-header">
                                                    <div>
                                                        <h3 class="text-lg font-semibold text-slate-900">Productos del pedido #{{ $pedido->id }}</h3>
                                                        <p class="text-sm text-slate-500">Detalle del pedido actual.</p>
                                                    </div>

                                                    <button class="btn-secondary" type="button" @click="open = false">Cerrar</button>
                                                </div>

                                                <div class="space-y-2">
                                                    @foreach($pedido->productos as $prod)
                                                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                                                            <span class="font-medium text-slate-700">{{ $prod->nombre }}</span>
                                                            <span class="text-slate-500">Cantidad {{ $prod->pivot->cantidad }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <form action="{{ route('pedidos.repetir', $pedido->id) }}" method="POST" class="mt-4">
                                                    @csrf
                                                    <button type="submit" class="btn-success w-full">Repetir pedido</button>
                                                </form>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </td>
                            <td class="estado-pedido px-4 py-4">
                                <x-estado-pedido :estado="$pedido->estado" />
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($pedido->factura && $pedido->factura->archivoPDF)
                                    <a href="{{ asset($pedido->factura->archivoPDF) }}" target="_blank" class="btn-primary inline-flex gap-2" title="Ver factura">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M7.5 3A1.5 1.5 0 006 4.5v15A1.5 1.5 0 007.5 21h9a1.5 1.5 0 001.5-1.5v-11.38a1.5 1.5 0 00-.44-1.06l-3.62-3.62A1.5 1.5 0 0012.88 3H7.5zm5.25 1.5v3a.75.75 0 00.75.75h3l-3.75-3.75z" />
                                        </svg>
                                        Factura
                                    </a>
                                @else
                                    <span class="text-sm text-slate-400">Sin factura</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
document.addEventListener('input', event => {
    if (!event.target.classList.contains('js-cantidad-producto')) {
        return;
    }

    const input = event.target;
    const checkbox = document.getElementById(input.dataset.checkboxId);

    if (!checkbox) {
        return;
    }

    const cantidad = Number(input.value);
    checkbox.checked = Number.isFinite(cantidad) && cantidad > 0;
});

document.addEventListener('change', event => {
    if (!event.target.matches('input[type="checkbox"][id^="producto-"]')) {
        return;
    }

    const checkbox = event.target;

    if (checkbox.checked) {
        return;
    }

    const input = document.querySelector(`.js-cantidad-producto[data-checkbox-id="${checkbox.id}"]`);

    if (input) {
        input.value = '';
    }
});

setInterval(() => {
    document.querySelectorAll('tr[data-pedido-id]').forEach(row => {
        const pedidoId = row.dataset.pedidoId;

        fetch(`/pedidos/${pedidoId}/documentos`)
            .then(res => res.json())
            .then(data => {
                if (data.estado_html) {
                    row.querySelector('.estado-pedido').innerHTML = data.estado_html;
                }
            });
    });
}, 5000);
</script>

@endsection
