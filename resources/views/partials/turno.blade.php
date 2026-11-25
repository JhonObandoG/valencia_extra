{{--
    Un turno del formulario de registro.
    $indice: posición (o "__INDICE__" en la plantilla), $turno: valores viejos, $abierto: si se ve el cuerpo.
--}}
@php
    $turno ??= [];
    $abierto ??= true;
    $error = fn ($campo) => is_int($indice) ? $errors->first("turnos.{$indice}.{$campo}") : null;
    $conError = is_int($indice) && collect($errors->keys())->contains(fn ($k) => str_starts_with($k, "turnos.{$indice}."));
@endphp

<div data-turno
     class="overflow-hidden rounded-lg border transition-all {{ $conError ? 'border-red-300' : 'border-gray-200' }}">

    {{-- Cabecera con el mini resumen: solo se ve con el turno colapsado. --}}
    <button type="button" data-cabecera
            class="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-pink-50/60 {{ $abierto ? 'hidden' : '' }}">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#d11e79] text-xs font-semibold text-white"
              data-numero>{{ is_int($indice) ? $indice + 1 : '' }}</span>
        <span class="flex-1 text-sm text-gray-700" data-resumen></span>
        <span class="text-xs font-medium text-[#d11e79]">Editar</span>
    </button>

    <div data-cuerpo class="{{ $abierto ? '' : 'hidden' }} space-y-4 border-l-4 border-[#d11e79] bg-white p-4">
        <div class="flex items-center justify-between">
            <p class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#d11e79] text-xs font-semibold text-white"
                      data-numero>{{ is_int($indice) ? $indice + 1 : '' }}</span>
                Turno
            </p>
            <button type="button" data-quitar class="action-btn action-btn-danger">
                <x-icono nombre="x" class="h-3.5 w-3.5" />
                Quitar
            </button>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="label-sm">Fecha</label>
                <input type="date" data-campo="fecha" name="turnos[{{ $indice }}][fecha]"
                       max="{{ now()->toDateString() }}" value="{{ $turno['fecha'] ?? '' }}"
                       class="input input-sm {{ $error('fecha') ? 'input-error' : '' }}">
                @if ($error('fecha'))
                    <p class="mt-1 text-xs text-red-600">{{ $error('fecha') }}</p>
                @endif
            </div>

            <div>
                <label class="label-sm">Hora inicio</label>
                <input type="time" data-campo="hora_inicio" name="turnos[{{ $indice }}][hora_inicio]"
                       value="{{ $turno['hora_inicio'] ?? '' }}"
                       class="input input-sm {{ $error('hora_inicio') ? 'input-error' : '' }}">
                @if ($error('hora_inicio'))
                    <p class="mt-1 text-xs text-red-600">{{ $error('hora_inicio') }}</p>
                @endif
            </div>

            <div>
                <label class="label-sm">Hora fin</label>
                <input type="time" data-campo="hora_fin" name="turnos[{{ $indice }}][hora_fin]"
                       value="{{ $turno['hora_fin'] ?? '' }}"
                       class="input input-sm {{ $error('hora_fin') ? 'input-error' : '' }}">
                @if ($error('hora_fin'))
                    <p class="mt-1 text-xs text-red-600">{{ $error('hora_fin') }}</p>
                @endif
            </div>
        </div>

        {{-- Vista previa del cálculo, con los mismos colores que las tablas. --}}
        <div data-calculo class="hidden flex flex-wrap items-center gap-x-3 gap-y-2 rounded-lg border border-[#d11e79]/20 bg-pink-50/60 px-3 py-2.5">
            <span class="text-xs font-medium uppercase tracking-wide text-gray-500">Se registrará como</span>
            <span data-calculo-total class="text-sm font-bold text-gray-900"></span>
            <div data-calculo-franjas class="flex flex-wrap gap-1"></div>
        </div>

        <label class="flex cursor-pointer items-start gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 transition-colors hover:border-[#d11e79]/40">
            <input type="checkbox" data-campo="es_festivo" name="turnos[{{ $indice }}][es_festivo]" value="1"
                   @checked(! empty($turno['es_festivo']))
                   class="mt-0.5 rounded border-gray-300 text-[#d11e79] focus:ring-[#d11e79]">
            <span class="text-sm text-gray-700">
                Ese día era festivo
                <span class="block text-xs text-gray-500">
                    Los domingos se detectan solos. Marca esto solo si fue un festivo entre semana.
                </span>
            </span>
        </label>

        <div>
            <label class="label-sm">
                Observación <span class="font-normal text-gray-400">(opcional)</span>
            </label>
            <textarea data-campo="observacion" name="turnos[{{ $indice }}][observacion]" rows="2"
                      class="input input-sm">{{ $turno['observacion'] ?? '' }}</textarea>
            @if ($error('observacion'))
                <p class="mt-1 text-xs text-red-600">{{ $error('observacion') }}</p>
            @endif
        </div>
    </div>
</div>
