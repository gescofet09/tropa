@extends('layouts.app')

@section('content')

<div class="space-y-6">
    <section class="panel">
        <h2 class="text-lg font-semibold text-slate-900">Panel de administración</h2>
        <p class="mt-1 text-sm text-slate-500">Total pedidos: {{ $pedidos->count() }}</p>
    </section>

    <section class="panel">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Todos los pedidos</h3>
            <p class="text-sm text-slate-500">Supervisión completa de clientes, repartidores y estados.</p>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Repartidor</th>
                        <th class="px-4 py-3">Seguimiento</th>
                        <th class="px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                @foreach ($pedidos as $pedido)
                    <tbody class="divide-y divide-slate-200">
                        <tr data-pedido-id="{{ $pedido->id }}" class="align-top">
                            <td class="px-4 py-4 font-semibold text-slate-700">#{{ $pedido->id }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $pedido->cliente->name ?? 'Sin cliente' }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $pedido->repartidor->name ?? 'Sin asignar' }}</td>
                            <td class="estado-pedido px-4 py-4"><x-estado-pedido :estado="$pedido->estado" /></td>
                            <td class="px-4 py-4" x-data="{ open: false }" @keydown.escape.window="open = false">
                                <div class="flex flex-wrap gap-2">
                                    <button class="btn-primary" type="button" @click="open = true">Ver pedido</button>

                                    <form action="{{ route('pedidos.destroy', $pedido->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-danger" type="submit">Eliminar</button>
                                    </form>
                                </div>
                                <template x-teleport="body">
                                    <div x-show="open" x-cloak>
                                        <div class="modal-overlay" @click="open = false"></div>

                                        <div class="modal-panel max-w-3xl max-h-[85vh] overflow-y-auto" @click.stop>
                                            <div class="modal-header">
                                                <div>
                                                    <h3 class="text-lg font-semibold text-slate-900">Pedido #{{ $pedido->id }}</h3>
                                                    <p class="text-sm text-slate-500">Cliente: {{ $pedido->cliente->name ?? 'Sin cliente' }} · Repartidor: {{ $pedido->repartidor->name ?? 'Sin asignar' }}</p>
                                                </div>

                                                <button class="btn-secondary" type="button" @click="open = false">Cerrar</button>
                                            </div>

                                            <div class="space-y-4">
                                                @foreach ($categorias as $categoria)
                                                    @php
                                                        $productosCategoria = $pedido->productos->where('categoria_id', $categoria->id);
                                                    @endphp
                                                    @if($productosCategoria->isNotEmpty())
                                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                            <h4 class="font-semibold text-slate-800">{{ $categoria->nombre }}</h4>
                                                            <ul class="mt-2 space-y-1 text-sm text-slate-600">
                                                                @foreach ($productosCategoria as $producto)
                                                                    <li>{{ $producto->nombre }} - Cantidad: {{ $producto->pivot->cantidad }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </td>
                        </tr>

                    </tbody>
                @endforeach
            </table>
        </div>
    </section>
</div>

<script>
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
