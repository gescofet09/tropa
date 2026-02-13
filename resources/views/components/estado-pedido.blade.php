@props(['estado'])

@php
// Lista de estados en orden
$estados = ['recibido', 'preparación', 'reparto', 'entregado'];

// Colores según el estado
$colores = [
    'recibido' => 'bg-secondary text-white',
    'preparación' => 'bg-warning text-dark',
    'reparto' => 'bg-info text-dark',
    'entregado' => 'bg-success text-white',
];

$estadoActual = $estado;
@endphp

<div class="d-flex justify-content-between align-items-center">
    @foreach ($estados as $estadoItem)
        @php
            $indexActual = array_search($estadoActual, $estados);
            $indexItem = array_search($estadoItem, $estados);
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
            {{ ucfirst($estadoItem) }}
        </div>
    @endforeach
</div>
