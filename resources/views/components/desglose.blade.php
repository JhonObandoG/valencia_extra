@props(['registro'])

{{-- Mismos colores que la vista previa del formulario de registro. --}}
<div class="flex flex-wrap gap-1">
    @if ($registro->horas_diurnas > 0)
        <span class="badge whitespace-nowrap bg-amber-100 text-amber-800">{{ horas($registro->horas_diurnas) }} diurna</span>
    @endif
    @if ($registro->horas_nocturnas > 0)
        <span class="badge whitespace-nowrap bg-indigo-100 text-indigo-800">{{ horas($registro->horas_nocturnas) }} nocturna</span>
    @endif
    @if ($registro->horas_dominicales > 0)
        <span class="badge whitespace-nowrap bg-pink-100 text-[#d11e79]">{{ horas($registro->horas_dominicales) }} dominical</span>
    @endif
</div>
