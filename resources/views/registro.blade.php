@extends('layouts.publico')

@section('titulo', 'Registrar horas extra')

@php
    // Si la validación falló, se repintan los turnos enviados y se abre el primero con error.
    $turnos = array_values(old('turnos', [[]]));
    $abierto = count($turnos) - 1;
    foreach (array_keys($turnos) as $i) {
        if (collect($errors->keys())->contains(fn ($k) => str_starts_with($k, "turnos.{$i}."))) {
            $abierto = $i;
            break;
        }
    }
@endphp

@section('contenido')
    <div>
        <div class="brand-header animate-slide-up mb-6">
            <h1 class="relative text-2xl font-bold md:text-3xl">Registro de horas extra</h1>
            <p class="relative mt-2 max-w-lg text-sm text-white/80">
                Pon tus datos una sola vez y agrega todos los turnos que necesites.
                Cada turno se guarda como un registro aparte.
            </p>
        </div>

        <form method="POST" action="{{ route('registro.store') }}" id="formulario"
              class="card card-elevated space-y-6">
            @csrf
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="documento" class="label">Documento</label>
                    <input type="text" name="documento" id="documento" value="{{ old('documento') }}" required
                           class="input @error('documento') input-error @enderror">
                    @error('documento')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nombre" class="label">Nombre completo</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                           class="input @error('nombre') input-error @enderror">
                    @error('nombre')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <hr class="border-gray-200">

            <div id="turnos" class="space-y-3">
                @foreach ($turnos as $i => $turno)
                    @include('partials.turno', ['indice' => $i, 'turno' => $turno, 'abierto' => $i === $abierto])
                @endforeach
            </div>

            <button type="button" id="agregar"
                    class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 transition-all hover:border-[#d11e79] hover:bg-pink-50 hover:text-[#d11e79]">
                <x-icono nombre="plus" />
                Agregar otro registro
            </button>

            <div class="flex items-start gap-2 rounded-lg bg-gray-50 px-3 py-2.5 text-xs text-gray-500">
                <x-icono nombre="reloj" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" />
                <span>
                    La franja nocturna va de 7:00 p. m. a 6:00 a. m.
                    &mdash; si la hora de fin es menor que la de inicio, se entiende que el turno pasó de la medianoche.
                </span>
            </div>

            <button type="submit"
                    class="btn btn-primary w-full py-2.5">
                <span id="conteo-envio">Enviar</span>
            </button>
        </form>

        @guest
            <p class="mt-6 text-center text-sm text-gray-500">
                <a href="{{ route('login') }}" class="font-medium text-[#d11e79] underline transition-colors hover:text-[#b01a66]">
                    Ingresar como administrador o consultor
                </a>
            </p>
        @endguest
    </div>

    {{-- Plantilla para los turnos que se agregan con "Agregar otro registro". --}}
    <template id="plantilla-turno">
        @include('partials.turno', ['indice' => '__INDICE__', 'turno' => [], 'abierto' => true])
    </template>
@endsection

@push('scripts')
        <script>
            const NOCTURNA_INICIO = @json(config('horas.nocturna_inicio'));
            const NOCTURNA_FIN = @json(config('horas.nocturna_fin'));
            const MAX_TURNOS = @json($maxTurnos);

            const contenedor = document.getElementById('turnos');
            const plantilla = document.getElementById('plantilla-turno');
            const btnAgregar = document.getElementById('agregar');
            const conteoEnvio = document.getElementById('conteo-envio');

            const DIAS = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];

            const aMinutos = (hhmm) => {
                const [h, m] = hhmm.split(':').map(Number);
                return h * 60 + m;
            };

            const formatear = (h) => Number(h.toFixed(2)).toString();

            // toISOString() pasa a UTC y correria el dia; se arma en hora local.
            const fechaLocal = (d) =>
                `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

            /** Espejo en el navegador de CalculadoraFranjas. El calculo que vale es el del servidor. */
            function repartir(fecha, inicio, fin, esFestivo) {
                const desde = new Date(`${fecha}T${inicio}:00`);
                const hasta = new Date(`${fecha}T${fin}:00`);
                if (hasta <= desde) hasta.setDate(hasta.getDate() + 1);

                const nocIni = aMinutos(NOCTURNA_INICIO);
                const nocFin = aMinutos(NOCTURNA_FIN);
                const franjas = { diurnas: 0, nocturnas: 0, dominicales: 0 };

                for (let t = new Date(desde); t < hasta; t.setMinutes(t.getMinutes() + 1)) {
                    const minuto = t.getHours() * 60 + t.getMinutes();

                    if (t.getDay() === 0 || (esFestivo && fechaLocal(t) === fecha)) {
                        franjas.dominicales += 1 / 60;
                    } else if (minuto >= nocIni || minuto < nocFin) {
                        franjas.nocturnas += 1 / 60;
                    } else {
                        franjas.diurnas += 1 / 60;
                    }
                }

                return { total: (hasta - desde) / 3600000, ...franjas };
            }

            const valorDe = (panel, campo) => panel.querySelector(`[data-campo="${campo}"]`);

            function datosDe(panel) {
                return {
                    fecha: valorDe(panel, 'fecha').value,
                    inicio: valorDe(panel, 'hora_inicio').value,
                    fin: valorDe(panel, 'hora_fin').value,
                    festivo: valorDe(panel, 'es_festivo').checked,
                };
            }

            const estaCompleto = (d) => d.fecha && d.inicio && d.fin && d.inicio !== d.fin;

            // Mismos colores que el componente x-desglose de las tablas.
            const FRANJAS = {
                diurnas: { etiqueta: 'diurna', clases: 'bg-amber-100 text-amber-800' },
                nocturnas: { etiqueta: 'nocturna', clases: 'bg-indigo-100 text-indigo-800' },
                dominicales: { etiqueta: 'dominical', clases: 'bg-pink-100 text-[#d11e79]' },
            };

            function actualizarResumen(panel) {
                const d = datosDe(panel);
                // El resumen vive en la cabecera, que solo se ve al colapsar el turno.
                const escribir = (texto) => (panel.querySelector('[data-resumen]').textContent = texto);
                const calculo = panel.querySelector('[data-calculo]');

                if (!estaCompleto(d)) {
                    escribir('Turno sin completar');
                    calculo.classList.add('hidden');
                    return;
                }

                const r = repartir(d.fecha, d.inicio, d.fin, d.festivo);
                const conHoras = Object.keys(FRANJAS).filter((k) => r[k] > 0.001);

                // Etiquetas de color dentro del panel abierto.
                const contenedorFranjas = panel.querySelector('[data-calculo-franjas]');
                panel.querySelector('[data-calculo-total]').textContent = `${formatear(r.total)} h`;
                contenedorFranjas.replaceChildren(
                    ...conHoras.map((k) => {
                        const chip = document.createElement('span');
                        chip.className = `badge whitespace-nowrap ${FRANJAS[k].clases}`;
                        chip.textContent = `${formatear(r[k])} ${FRANJAS[k].etiqueta}`;
                        return chip;
                    })
                );
                calculo.classList.remove('hidden');

                // Resumen de una linea para cuando el turno se colapsa.
                const [a, m, dia] = d.fecha.split('-').map(Number);
                const nombreDia = DIAS[new Date(a, m - 1, dia).getDay()];

                escribir(
                    `${nombreDia} ${String(dia).padStart(2, '0')}/${String(m).padStart(2, '0')} · ` +
                    `${d.inicio}-${d.fin} · ${formatear(r.total)} h ` +
                    `(${conHoras.map((k) => `${formatear(r[k])} ${k}`).join(', ')})`
                );
            }

            const paneles = () => [...contenedor.querySelectorAll('[data-turno]')];

            function abrir(panel) {
                paneles().forEach((p) => {
                    const esEste = p === panel;
                    p.querySelector('[data-cuerpo]').classList.toggle('hidden', !esEste);
                    p.querySelector('[data-cabecera]').classList.toggle('hidden', esEste);
                    actualizarResumen(p);
                });
            }

            function renumerar() {
                paneles().forEach((panel, i) => {
                    panel.querySelectorAll('[name]').forEach((campo) => {
                        campo.name = campo.name.replace(/turnos\[[^\]]*\]/, `turnos[${i}]`);
                    });
                    panel.querySelectorAll('[data-numero]').forEach((n) => (n.textContent = i + 1));
                });

                const total = paneles().length;
                conteoEnvio.textContent = total === 1 ? 'Enviar 1 registro' : `Enviar ${total} registros`;
                btnAgregar.disabled = total >= MAX_TURNOS;
                btnAgregar.classList.toggle('opacity-40', total >= MAX_TURNOS);
                // Con un solo turno no tiene sentido poder quitarlo.
                paneles().forEach((p) => p.querySelector('[data-quitar]').classList.toggle('hidden', total === 1));
            }

            btnAgregar.addEventListener('click', () => {
                if (paneles().length >= MAX_TURNOS) return;

                const html = plantilla.innerHTML.replaceAll('__INDICE__', paneles().length);
                contenedor.insertAdjacentHTML('beforeend', html);

                const nuevo = paneles().at(-1);
                renumerar();
                abrir(nuevo);
                valorDe(nuevo, 'fecha').focus();
                nuevo.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });

            // Delegacion: sirve igual para los paneles pintados por Blade y los clonados.
            contenedor.addEventListener('click', (e) => {
                const cabecera = e.target.closest('[data-cabecera]');
                if (cabecera) abrir(cabecera.closest('[data-turno]'));

                const quitar = e.target.closest('[data-quitar]');
                if (quitar) {
                    const panel = quitar.closest('[data-turno]');
                    const siguiente = panel.nextElementSibling ?? panel.previousElementSibling;
                    panel.remove();
                    renumerar();
                    if (siguiente) abrir(siguiente);
                }
            });

            contenedor.addEventListener('change', (e) => {
                const panel = e.target.closest('[data-turno]');
                if (panel) actualizarResumen(panel);
            });

            // Los campos del turno no llevan "required": estando ocultos el navegador
            // bloquearia el envio sin poder mostrar el aviso. Se revisa aca.
            document.getElementById('formulario').addEventListener('submit', (e) => {
                const incompleto = paneles().find((p) => !estaCompleto(datosDe(p)));
                if (!incompleto) return;

                e.preventDefault();
                abrir(incompleto);
                incompleto.scrollIntoView({ behavior: 'smooth', block: 'center' });

                const d = datosDe(incompleto);
                const faltante =
                    (!d.fecha && valorDe(incompleto, 'fecha')) ||
                    (!d.inicio && valorDe(incompleto, 'hora_inicio')) ||
                    valorDe(incompleto, 'hora_fin');
                faltante.focus();
            });

            paneles().forEach(actualizarResumen);
            renumerar();
        </script>
@endpush
