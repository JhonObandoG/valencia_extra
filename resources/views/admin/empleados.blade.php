@extends('layouts.panel')

@section('titulo', 'Empleados')
@section('subtitulo', 'Se crean solos al registrar horas con un documento nuevo')

@section('cabecera')
    <span class="badge badge-neutral px-3 py-1 text-sm">
        {{ $total }} {{ $total === 1 ? 'empleado' : 'empleados' }}
    </span>
@endsection

@section('contenido')
    <form method="GET" class="mb-6 flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por nombre o documento"
               class="input max-w-sm">
        <button class="btn btn-primary">
            <x-icono nombre="buscar" />
            Buscar
        </button>
        @if ($q !== '')
            <a href="{{ route('admin.empleados') }}" class="btn btn-ghost">Limpiar</a>
        @endif
    </form>

    <x-avisos />

    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Horas aprobadas</th>
                    <th>Pendientes</th>
                    <th>Estado</th>
                    <th class="text-right">Editar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($empleados as $empleado)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-pink-100 text-sm font-semibold text-[#d11e79]">
                                    {{ mb_strtoupper(mb_substr($empleado->nombre, 0, 1)) }}
                                </span>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $empleado->nombre }}</div>
                                    <div class="text-xs text-gray-500">{{ $empleado->documento }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="font-semibold text-gray-900">{{ horas($empleado->horas_aprobadas) }} h</td>
                        <td>
                            @if ($empleado->pendientes > 0)
                                <span class="badge badge-pending">{{ $empleado->pendientes }} por revisar</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $empleado->activo ? 'badge-approved' : 'badge-neutral' }}">
                                {{ $empleado->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <button type="button" data-editar
                                    data-accion="{{ route('admin.empleados.update', $empleado) }}"
                                    data-id="{{ $empleado->id }}"
                                    data-nombre="{{ $empleado->nombre }}"
                                    data-documento="{{ $empleado->documento }}"
                                    data-activo="{{ $empleado->activo ? '1' : '0' }}"
                                    class="action-btn action-btn-primary">
                                Editar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <x-icono nombre="usuarios" class="empty-state-icon" />
                                <p class="mt-3 text-sm text-gray-500">
                                    {{ $q !== '' ? "Ningún empleado coincide con \"{$q}\"." : 'Todavía no hay empleados. Aparecen solos con el primer registro de horas.' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $empleados->links() }}</div>

    <dialog id="modal-empleado"
            class="animate-scale-in m-auto w-full max-w-md rounded-xl border-0 p-0 shadow-2xl backdrop:bg-[#2d001e]/60">
        <form method="POST" id="form-empleado">
            @csrf
            @method('PUT')
            <input type="hidden" name="_id" id="emp-id">
            <div class="bg-gradient-to-r from-[#2d001e] to-[#d11e79] px-6 py-4">
                <h2 class="text-lg font-bold text-white">Editar empleado</h2>
                <p class="mt-0.5 text-sm text-white/70">Corrige el nombre o el documento si vinieron mal escritos.</p>
            </div>
            <div class="space-y-4 p-6">
                <div>
                    <label for="emp-nombre" class="label">Nombre completo</label>
                    <input type="text" name="nombre" id="emp-nombre" required maxlength="255" class="input">
                </div>
                <div>
                    <label for="emp-documento" class="label">Documento</label>
                    <input type="text" name="documento" id="emp-documento" required maxlength="30" class="input">
                </div>
                <label class="flex cursor-pointer items-start gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                    <input type="checkbox" name="activo" id="emp-activo" value="1"
                           class="mt-0.5 rounded border-gray-300 text-[#d11e79] focus:ring-[#d11e79]">
                    <span class="text-sm text-gray-700">
                        Activo
                        <span class="block text-xs text-gray-500">
                            Los inactivos siguen apareciendo en la consulta con su historial.
                        </span>
                    </span>
                </label>
                <div class="flex justify-end gap-2">
                    <button type="button" data-cancelar class="btn btn-ghost btn-sm">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                </div>
            </div>
        </form>
    </dialog>
@endsection

@push('scripts')
    <script>
        const modalEmpleado = document.getElementById('modal-empleado');
        const formEmpleado = document.getElementById('form-empleado');
        const campos = {
            id: document.getElementById('emp-id'),
            nombre: document.getElementById('emp-nombre'),
            documento: document.getElementById('emp-documento'),
            activo: document.getElementById('emp-activo'),
        };

        function abrirEmpleado({ accion, id, nombre, documento, activo }) {
            formEmpleado.action = accion;
            campos.id.value = id;
            campos.nombre.value = nombre;
            campos.documento.value = documento;
            campos.activo.checked = activo === '1';
            modalEmpleado.showModal();
            campos.nombre.focus();
        }

        document.addEventListener('click', (e) => {
            const boton = e.target.closest('[data-editar]');
            if (!boton) return;
            abrirEmpleado({ accion: boton.dataset.accion, ...boton.dataset });
        });

        modalEmpleado.querySelector('[data-cancelar]').addEventListener('click', () => modalEmpleado.close());

        // Si el guardado falló, se reabre el mismo empleado con lo que se había escrito.
        @if ($errors->any() && old('_id'))
            (() => {
                const fila = document.querySelector('[data-editar][data-id="{{ (int) old('_id') }}"]');
                if (!fila) return;
                abrirEmpleado({
                    accion: fila.dataset.accion,
                    id: fila.dataset.id,
                    nombre: @json(old('nombre')),
                    documento: @json(old('documento')),
                    activo: @json(old('activo') ? '1' : '0'),
                });
            })();
        @endif
    </script>
@endpush
