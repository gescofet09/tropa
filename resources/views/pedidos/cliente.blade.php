@extends('layouts.app')

@section('content')

@php
    $categoriasVue = $categorias->map(function ($categoria) {
        return [
            'id' => $categoria->id,
            'nombre' => $categoria->nombre,
            'productos' => $categoria->productos->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'precio' => $producto->precio,
                    'unidad' => $producto->unidad,
                    'stock' => $producto->stock,
                ];
            })->values(),
        ];
    })->values();
@endphp

<div class="space-y-6">
    <section class="panel">
        <div class="mt-4 space-y-3">
            @if(session('error'))
                <div class="alert-danger">{{ session('error') }}</div>
            @endif
        </div>

        <div
            id="pedido-builder-root"
            data-categories='@json($categoriasVue)'
            data-store-url="{{ route('pedidos.store') }}"
            data-csrf-token="{{ csrf_token() }}"
        ></div>

        <div class="mt-4 space-y-3">
            @if(session('success'))
                <div class="alert-success">
                    {{ session('success') }}
                </div>
            @endif
        </div>
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
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Seguimiento</th>
                        <th class="px-4 py-3 text-center align-middle">Documentos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($pedidos as $pedido)
                        <tr data-pedido-id="{{ $pedido->id }}" class="align-top">
                            <td class="px-4 py-4 font-semibold text-slate-700">#{{ $pedido->id }}</td>
                            <td class="px-4 py-4">
                                <div class="space-y-3">
                                    <button
                                        class="btn-primary btn-base !px-3 !py-1.5 text-xs"
                                        type="button"
                                        data-modal-open="cliente-pedido-{{ $pedido->id }}"
                                    >Ver productos</button>

                                    <div id="cliente-pedido-{{ $pedido->id }}" class="hidden" data-modal>
                                        <div class="modal-overlay" data-modal-close></div>

                                        <div class="modal-panel max-w-2xl">
                                            <div class="modal-header">
                                                <div>
                                                    <h3 class="text-lg font-semibold text-slate-900">Productos del pedido #{{ $pedido->id }}</h3>
                                                    <p class="text-sm text-slate-500">Detalle del pedido actual.</p>
                                                </div>

                                                <button class="btn-secondary" type="button" data-modal-close>Cerrar</button>
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
                                </div>
                            </td>
                            <td class="px-4 py-4 font-semibold text-slate-700">
                                {{ number_format((float) $pedido->total, 2, ',', '.') }} €
                            </td>
                            <td class="estado-pedido px-4 py-4">
                                <x-estado-pedido :estado="$pedido->estado" />
                            </td>
                            <td class="px-4 py-4 text-center align-middle">
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

        @if (method_exists($pedidos, 'hasPages') && $pedidos->hasPages())
            <div class="mt-6">
                {{ $pedidos->links() }}
            </div>
        @endif
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
