@extends('layouts.panel')

@section('titulo', 'Consulta')
@section('subtitulo', 'Todas las horas extra registradas, con sus filtros')

@section('cabecera')
    <span class="badge badge-neutral px-3 py-1 text-sm">
        {{ $registros->total() }} {{ $registros->total() === 1 ? 'registro' : 'registros' }}
    </span>
@endsection

@section('contenido')
    @php
        $tarjetas = [
            ['Total horas', $totales->total, true],
            ['Diurna', $totales->diurnas, false],
            ['Nocturna', $totales->nocturnas, false],
            ['Dominical', $totales->dominicales, false],
        ];
    @endphp

    <div class="animate-slide-up mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($tarjetas as $i => [$titulo, $valor, $destacada])
            <div class="rounded-lg border border-gray-200 p-5 shadow-sm {{ $destacada ? 'bg-gradient-to-br from-[#2d001e] to-[#d11e79] text-white border-transparent shadow-lg' : 'bg-white text-gray-900' }}"
                 style="animation-delay: {{ $i * 60 }}ms">
                <p class="text-xs font-medium uppercase tracking-wider {{ $destacada ? 'text-white/60' : 'text-gray-500' }}">{{ $titulo }}</p>
                <p class="mt-1 text-3xl font-bold">{{ horas($valor) }}
                    <span class="ml-1 text-lg font-normal opacity-60">h</span></p>
            </div>
        @endforeach
    </div>

    <form method="GET" class="card mb-6">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="desde" class="label-sm">Desde</label>
                <input type="date" name="desde" id="desde" value="{{ $filtros['desde'] ?? '' }}" class="input input-sm">
            </div>
            <div>
                <label for="hasta" class="label-sm">Hasta</label>
                <input type="date" name="hasta" id="hasta" value="{{ $filtros['hasta'] ?? '' }}" class="input input-sm">
            </div>
            <div>
                <label for="franja" class="label-sm">Franja</label>
                <select name="franja" id="franja" class="input input-sm">
                    <option value="">Todas</option>
                    @foreach (array_keys(\App\Models\HoraExtra::FRANJAS) as $franja)
                        <option value="{{ $franja }}" @selected(($filtros['franja'] ?? '') === $franja)>{{ ucfirst($franja) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="estado" class="label-sm">Estado</label>
                <select name="estado" id="estado" class="input input-sm">
                    <option value="">Todos</option>
                    @foreach (\App\Models\HoraExtra::ESTADOS as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected(($filtros['estado'] ?? '') === $valor)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="horas_min" class="label-sm">Horas mín.</label>
                <input type="number" step="0.5" min="0" name="horas_min" id="horas_min"
                       value="{{ $filtros['horas_min'] ?? '' }}" class="input input-sm">
            </div>
            <div>
                <label for="horas_max" class="label-sm">Horas máx.</label>
                <input type="number" step="0.5" min="0" name="horas_max" id="horas_max"
                       value="{{ $filtros['horas_max'] ?? '' }}" class="input input-sm">
            </div>
            <div class="lg:col-span-2">
                <label for="empleado" class="label-sm">Empleado</label>
                <input type="text" name="empleado" id="empleado" placeholder="Nombre o documento"
                       value="{{ $filtros['empleado'] ?? '' }}" class="input input-sm">
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <button class="btn btn-primary btn-sm">
                <x-icono nombre="buscar" />
                Filtrar
            </button>
            <a href="{{ route('consulta') }}" class="btn btn-ghost btn-sm">Limpiar</a>
        </div>
    </form>

    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Fecha y horario</th>
                    <th>Total</th>
                    <th>Desglose</th>
                    <th>Estado</th>
                    <th>Revisado por</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($registros as $registro)
                    <tr class="align-top">
                        <td>
                            <div class="font-medium text-gray-900">{{ $registro->empleado->nombre }}</div>
                            <div class="text-xs text-gray-500">{{ $registro->empleado->documento }}</div>
                            @if ($registro->observacion)
                                <p class="mt-1 max-w-xs text-xs italic text-gray-600">{{ $registro->observacion }}</p>
                            @endif
                        </td>
                        <x-horario :registro="$registro" />
                        <td class="whitespace-nowrap font-semibold text-gray-900">{{ horas($registro->horas_total) }} h</td>
                        <td><x-desglose :registro="$registro" /></td>
                        <td><x-estado :estado="$registro->estado" /></td>
                        <td class="text-gray-600">
                            {{ $registro->revisor?->name ?? '-' }}
                            @if ($registro->revisado_at)
                                <div class="text-xs text-gray-400">{{ $registro->revisado_at->format('d/m/Y H:i') }}</div>
                            @endif
                            @if ($registro->motivo_rechazo)
                                <p class="mt-1 max-w-xs text-xs italic text-red-600">{{ $registro->motivo_rechazo }}</p>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <x-icono nombre="bandeja" class="empty-state-icon" />
                                <p class="mt-3 text-sm text-gray-500">No hay registros con esos filtros.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $registros->links() }}</div>
@endsection
