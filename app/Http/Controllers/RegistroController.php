<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\HoraExtra;
use App\Services\CalculadoraFranjas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistroController extends Controller
{
    public function create()
    {
        return view('registro', [
            'maxTurnos' => config('horas.max_turnos'),
        ]);
    }

    public function store(Request $request, CalculadoraFranjas $calculadora)
    {
        $datos = $request->validate([
            'documento' => ['required', 'string', 'max:30'],
            'nombre' => ['required', 'string', 'max:255'],
            'turnos' => ['required', 'array', 'min:1', 'max:'.config('horas.max_turnos')],
            'turnos.*.fecha' => ['required', 'date', 'before_or_equal:today'],
            'turnos.*.hora_inicio' => ['required', 'date_format:H:i'],
            'turnos.*.hora_fin' => ['required', 'date_format:H:i', 'different:turnos.*.hora_inicio'],
            'turnos.*.es_festivo' => ['nullable', 'boolean'],
            'turnos.*.observacion' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'turnos.*.fecha' => 'fecha',
            'turnos.*.hora_inicio' => 'hora de inicio',
            'turnos.*.hora_fin' => 'hora de fin',
            'turnos.*.observacion' => 'observación',
        ]);

        $totales = ['horas_total' => 0, 'horas_diurnas' => 0, 'horas_nocturnas' => 0, 'horas_dominicales' => 0];

        DB::transaction(function () use ($datos, $calculadora, &$totales) {
            // Los empleados se crean solos con el primer registro de un documento nuevo.
            $empleado = Empleado::firstOrCreate(
                ['documento' => trim($datos['documento'])],
                ['nombre' => trim($datos['nombre'])],
            );

            // Cada turno es un registro aparte, con los mismos datos del empleado.
            foreach ($datos['turnos'] as $turno) {
                $esFestivo = (bool) ($turno['es_festivo'] ?? false);
                $franjas = $calculadora->calcular($turno['fecha'], $turno['hora_inicio'], $turno['hora_fin'], $esFestivo);

                $empleado->horasExtra()->create([
                    'fecha' => $turno['fecha'],
                    'hora_inicio' => $turno['hora_inicio'],
                    'hora_fin' => $turno['hora_fin'],
                    'es_festivo' => $esFestivo,
                    'observacion' => $turno['observacion'] ?? null,
                    'estado' => HoraExtra::PENDIENTE,
                    ...$franjas,
                ]);

                foreach ($franjas as $campo => $valor) {
                    $totales[$campo] += $valor;
                }
            }
        });

        return redirect()->route('registro')->with('exito', $this->mensaje(count($datos['turnos']), $totales));
    }

    /** "Registro enviado: 4 horas en total (2 nocturnas, 2 dominicales). Queda pendiente de aprobación." */
    private function mensaje(int $cantidad, array $totales): string
    {
        $partes = collect([
            'diurnas' => $totales['horas_diurnas'],
            'nocturnas' => $totales['horas_nocturnas'],
            'dominicales' => $totales['horas_dominicales'],
        ])->filter(fn ($h) => $h > 0)->map(fn ($h, $franja) => horas($h).' '.$franja)->implode(', ');

        $inicio = $cantidad === 1 ? 'Registro enviado' : "Se enviaron {$cantidad} registros";
        $final = $cantidad === 1 ? 'Queda pendiente de aprobación.' : 'Quedan pendientes de aprobación.';

        return "{$inicio}: ".horas($totales['horas_total'])." horas en total ({$partes}). {$final}";
    }
}
