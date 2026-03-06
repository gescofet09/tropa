@props(['estado'])

@php
// Normalizamos sin acentos para evitar inconsistencias (preparacion/preparación).
$estados = ['recibido', 'preparacion', 'reparto', 'entregado'];

$colores = [
    'recibido' => 'bg-secondary text-white',
    'preparacion' => 'bg-warning text-dark',
    'reparto' => 'bg-info text-dark',
    'entregado' => 'bg-success text-white',
];

$labels = [
    'recibido' => 'Recibido',
    'preparacion' => 'Preparación',
    'reparto' => 'Reparto',
    'entregado' => 'Entregado',
];

$estadoActual = \Illuminate\Support\Str::slug($estado ?? '', '');
$indexActual = array_search($estadoActual, $estados, true);
if ($indexActual === false) {
    $indexActual = 0;
}
@endphp

<div class="d-flex justify-content-between align-items-center">
    @foreach ($estados as $estadoItem)
        @php
            $indexItem = array_search($estadoItem, $estados, true);
            $isPast = $indexItem < $indexActual;   // pasos completados
            $isCurrent = $estadoItem === $estadoActual; // paso actual
            $clase = $colores[$estadoItem];
            $style = '';
            if ($isCurrent) {
                $style = 'border: 2px solid #000; font-weight: bold;';
            }
            if (!$isPast && !$isCurrent) {
                $style .= ' opacity: 0.6;';
            }
        @endphp

        <div class="step px-3 py-1 rounded text-center {{ $clase }}" style="{{ $style }}; min-width: 100px;">
            {{ $labels[$estadoItem] }}
        </div>
    @endforeach
</div>
