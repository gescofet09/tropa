@php
// Lista de estados en orden
$estados = ['pedido recibido', 'preparación', 'en reparto', 'entregado'];

// Normaliza el estado actual
$estadoActual = $estado; // $estado viene del pedido
if($estadoActual === 'preparado') {
    $estadoActual = 'preparación';
}
@endphp

<div class="timeline">
@foreach ($estados as $estadoItem)
    @php
        $isActive = array_search($estadoActual, $estados) >= array_search($estadoItem, $estados);
        $isCurrent = $estadoActual === $estadoItem;
    @endphp
    <div class="step 
        @if($isActive) bg-success text-white @else bg-secondary text-white @endif
        @if($isCurrent) fw-bold border border-dark @endif
    ">
        {{ ucfirst($estadoItem) }}
    </div>
@endforeach
</div>
