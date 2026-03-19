@props(['estado'])

@php
$estados = ['recibido', 'preparacion', 'reparto', 'entregado'];

$colores = [
    'recibido' => 'bg-slate-500 text-white',
    'preparacion' => 'bg-amber-400 text-slate-900',
    'reparto' => 'bg-sky-400 text-slate-900',
    'entregado' => 'bg-emerald-500 text-white',
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

<div class="flex flex-wrap items-center gap-2">
    @foreach ($estados as $estadoItem)
        @php
            $indexItem = array_search($estadoItem, $estados, true);
            $isPast = $indexItem < $indexActual;
            $isCurrent = $estadoItem === $estadoActual;
            $clase = $colores[$estadoItem];
            $style = '';
            if ($isCurrent) {
                $style = 'font-weight: bold;';
            }
            if (!$isPast && !$isCurrent) {
                $style .= ' opacity: 0.6;';
            }
        @endphp

        <div class="min-w-[110px] rounded-full px-4 py-2 text-center text-xs font-medium sm:text-sm {{ $isPast || $isCurrent ? $clase : 'bg-slate-200 text-slate-500' }}" style="{{ $style }}">
            {{ $labels[$estadoItem] }}
        </div>
    @endforeach
</div>
