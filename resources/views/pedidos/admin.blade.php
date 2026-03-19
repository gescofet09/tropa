@extends('layouts.app')

@section('content')

<div class="space-y-6">
    <section class="panel">
        <h2 class="text-lg font-semibold text-slate-900">Panel de administración</h2>
        <p class="mt-1 text-sm text-slate-500">Total pedidos: {{ $pedidos->count() }}</p>
    </section>

    <section class="panel">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Todos los pedidos</h3>
                <p class="text-sm text-slate-500">Supervisión completa de clientes, repartidores y estados.</p>
            </div>
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
                <tbody class="divide-y divide-slate-200">
                    @foreach ($pedidos as $pedido)
                        <tr data-pedido-id="{{ $pedido->id }}" class="align-top" x-data="{ open: false }">
                            <td class="px-4 py-4 font-semibold text-slate-700">#{{ $pedido->id }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $pedido->cliente->name ?? 'Sin cliente' }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $pedido->repartidor->name ?? 'Sin asignar' }}</td>
                            <td class="estado-pedido px-4 py-4"><x-estado-pedido :estado="$pedido->estado" /></td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <button class="btn-primary" type="button" @click="open = !open" x-text="open ? 'Ocultar pedido' : 'Ver pedido'"></button>

                                    <form action="{{ route('pedidos.destroy', $pedido->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-danger" type="submit">Eliminar</button>
                                    </form>
                                </div>

                                <div x-show="open" class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    @foreach ($categorias as $categoria)
                                        @php
                                            $productosCategoria = $pedido->productos->where('categoria_id', $categoria->id);
                                        @endphp
                                        @if($productosCategoria->isNotEmpty())
                                            <div class="mb-4 last:mb-0">
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
                            </td>
                        </tr>
                    @endforeach
                </tbody>
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
