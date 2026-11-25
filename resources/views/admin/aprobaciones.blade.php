@extends('layouts.panel')

@section('titulo', 'Aprobaciones')
@section('subtitulo', 'Revisa los turnos reportados y aprueba o rechaza cada uno')

@section('cabecera')
    @if ($pendientes > 0)
        <span class="badge badge-pending px-3 py-1 text-sm">
            {{ $pendientes }} {{ $pendientes === 1 ? 'pendiente' : 'pendientes' }}
        </span>
    @endif
@endsection

@section('contenido')
    <x-avisos />

    <form method="GET" class="card mb-6">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
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
                    @foreach (\App\Models\HoraExtra::ESTADOS as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected($filtros['estado'] === $valor)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="empleado" class="label-sm">Empleado</label>
                <input type="text" name="empleado" id="empleado" placeholder="Nombre o documento"
                       value="{{ $filtros['empleado'] ?? '' }}" class="input input-sm">
            </div>
        </div>
        <div class="mt-4">
            <button class="btn btn-primary btn-sm">
                <x-icono nombre="buscar" />
                Filtrar
            </button>
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
                    <th>Acción</th>
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
                        <td>
                            <x-estado :estado="$registro->estado" />
                            @if ($registro->motivo_rechazo)
                                <p class="mt-1 max-w-xs text-xs italic text-red-600">{{ $registro->motivo_rechazo }}</p>
                            @endif
                        </td>
                        <td>
                            @if ($registro->estado === \App\Models\HoraExtra::PENDIENTE)
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('admin.aprobaciones.aprobar', $registro) }}">
                                        @csrf
                                        <button class="action-btn action-btn-success">
                                            <x-icono nombre="check" class="h-3.5 w-3.5" />
                                            Aprobar
                                        </button>
                                    </form>
                                    <button type="button" data-rechazar
                                            data-accion="{{ route('admin.aprobaciones.rechazar', $registro) }}"
                                            data-empleado="{{ $registro->empleado->nombre }}"
                                            data-detalle="{{ $registro->detalle() }}"
                                            class="action-btn action-btn-danger">
                                        <x-icono nombre="x" class="h-3.5 w-3.5" />
                                        Rechazar
                                    </button>
                                </div>
                            @else
                                <div class="text-sm text-gray-600">{{ $registro->revisor?->name ?? '-' }}</div>
                                @if ($registro->revisado_at)
                                    <div class="text-xs text-gray-400">{{ $registro->revisado_at->format('d/m/Y H:i') }}</div>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <x-icono nombre="check" class="empty-state-icon" />
                                <p class="mt-3 text-sm text-gray-500">
                                    {{ $filtros['estado'] === \App\Models\HoraExtra::PENDIENTE ? 'No hay turnos por revisar.' : 'No hay registros con esos filtros.' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $registros->links() }}</div>

    <dialog id="modal-rechazo"
            class="animate-scale-in m-auto w-full max-w-md rounded-xl border-0 p-0 shadow-2xl backdrop:bg-[#2d001e]/60">
        <form method="POST" id="form-rechazo">
            @csrf
            <div class="bg-gradient-to-r from-[#2d001e] to-[#d11e79] px-6 py-4">
                <h2 class="text-lg font-bold text-white">Rechazar registro</h2>
                <p class="mt-0.5 text-sm text-white/70" data-contexto></p>
            </div>
            <div class="space-y-4 p-6">
                <div>
                    <label for="motivo_rechazo" class="label">Motivo del rechazo</label>
                    <textarea name="motivo_rechazo" id="motivo_rechazo" rows="3" required maxlength="1000"
                              placeholder="Explica por qué se rechaza. El empleado lo verá en la consulta."
                              class="input"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" data-cancelar class="btn btn-ghost btn-sm">Cancelar</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <x-icono nombre="x" class="h-3.5 w-3.5" />
                        Rechazar
                    </button>
                </div>
            </div>
        </form>
    </dialog>
@endsection

@push('scripts')
    <script>
        const modal = document.getElementById('modal-rechazo');
        const formRechazo = document.getElementById('form-rechazo');
        const contexto = modal.querySelector('[data-contexto]');
        const motivo = document.getElementById('motivo_rechazo');

        document.addEventListener('click', (e) => {
            const boton = e.target.closest('[data-rechazar]');
            if (!boton) return;
            formRechazo.action = boton.dataset.accion;
            contexto.textContent = `${boton.dataset.empleado} · ${boton.dataset.detalle}`;
            motivo.value = '';
            modal.showModal();
            motivo.focus();
        });

        modal.querySelector('[data-cancelar]').addEventListener('click', () => modal.close());
    </script>
@endpush
