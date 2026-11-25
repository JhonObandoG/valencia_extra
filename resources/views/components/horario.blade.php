@props(['registro'])

{{-- Fecha y horario; "(+1 día)" cuando el turno pasa de la medianoche. --}}
<td class="whitespace-nowrap">
    {{ $registro->fecha->format('d/m/Y') }}
    <div class="text-xs text-gray-500">
        {{ $registro->inicio() }} - {{ $registro->fin() }}
        @if ($registro->cruzaMedianoche())
            <span class="text-gray-400">(+1 día)</span>
        @endif
    </div>
    @if ($registro->es_festivo)
        <span class="badge badge-info mt-1">Festivo</span>
    @endif
</td>
