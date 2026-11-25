<?php

namespace Database\Seeders;

use App\Models\Empleado;
use App\Models\User;
use App\Services\CalculadoraFranjas;
use Illuminate\Database\Seeder;

/**
 * Datos de demo.
 * Usuarios: admin@valencia-extra.test y consultor@valencia-extra.test, clave "password".
 */
class DatabaseSeeder extends Seeder
{
    public function run(CalculadoraFranjas $calculadora): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@valencia-extra.test'],
            ['name' => 'Administrador', 'rol' => 'admin', 'password' => 'password'],
        );

        User::updateOrCreate(
            ['email' => 'consultor@valencia-extra.test'],
            ['name' => 'Consultor', 'rol' => 'consultor', 'password' => 'password'],
        );

        $empleados = collect([
            '1094782341' => 'Ana Restrepo Vélez',
            '1017554902' => 'Camilo Arango Ruiz',
            '43128776' => 'Luz Marina Ospina',
            '1152003488' => 'Jhon Freddy Zapata',
            '71654320' => 'Diego Cardona Mesa',
        ])->map(fn ($nombre, $documento) => Empleado::updateOrCreate(['documento' => (string) $documento], ['nombre' => $nombre]));

        // documento, fecha, inicio, fin, festivo, observación, estado, motivo de rechazo
        $turnos = [
            ['1094782341', '2026-09-07', '19:00', '23:00', false, 'Cierre de inventario', 'aprobada', null],
            ['1094782341', '2026-09-04', '20:00', '01:00', false, 'Despacho urgente a Bogotá', 'aprobada', null],
            ['1094782341', '2026-09-06', '08:00', '14:00', false, 'Recibo de contenedor', 'aprobada', null],
            ['1094782341', '2026-09-08', '18:00', '21:30', false, null, 'pendiente', null],
            ['1017554902', '2026-09-06', '22:00', '06:00', false, 'Turno de relevo', 'aprobada', null],
            ['1017554902', '2026-09-05', '22:00', '02:00', false, 'Sábado que entra al domingo', 'aprobada', null],
            ['1017554902', '2026-09-01', '17:00', '22:00', false, null, 'rechazada', 'No hay autorización previa del jefe de área.'],
            ['1017554902', '2026-09-05', '19:00', '23:00', false, 'Apoyo línea 3', 'pendiente', null],
            ['43128776', '2026-09-03', '06:00', '09:00', false, 'Apertura anticipada', 'aprobada', null],
            ['43128776', '2026-09-06', '14:00', '20:00', false, 'Turno dominical completo', 'aprobada', null],
            ['43128776', '2026-08-31', '13:00', '15:30', true, 'Festivo entre semana', 'aprobada', null],
            ['43128776', '2026-09-08', '19:30', '22:00', false, null, 'pendiente', null],
            ['1152003488', '2026-09-02', '02:00', '08:00', false, 'Falla en compresor', 'aprobada', null],
            ['1152003488', '2026-08-30', '23:00', '03:00', false, 'Mantenimiento programado', 'aprobada', null],
            ['1152003488', '2026-09-07', '04:00', '07:00', false, 'Cambio de rodamientos', 'pendiente', null],
            ['1152003488', '2026-09-05', '09:00', '13:00', false, null, 'rechazada', 'El horario se cruza con la jornada ordinaria.'],
            ['71654320', '2026-08-29', '16:00', '21:00', false, 'Ruta Medellín - Pereira', 'aprobada', null],
            ['71654320', '2026-09-06', '05:00', '11:00', false, 'Ruta dominical', 'pendiente', null],
            ['71654320', '2026-09-06', '18:00', '20:15', false, null, 'aprobada', null],
        ];

        foreach ($turnos as [$documento, $fecha, $inicio, $fin, $festivo, $observacion, $estado, $motivo]) {
            $revisado = $estado !== 'pendiente';

            $empleados[$documento]->horasExtra()->updateOrCreate(
                ['fecha' => $fecha, 'hora_inicio' => $inicio],
                [
                    'hora_fin' => $fin,
                    'es_festivo' => $festivo,
                    'observacion' => $observacion,
                    'estado' => $estado,
                    'revisado_por' => $revisado ? $admin->id : null,
                    'revisado_at' => $revisado ? date('Y-m-d 09:15:00', strtotime($fecha.' +1 day')) : null,
                    'motivo_rechazo' => $motivo,
                    ...$calculadora->calcular($fecha, $inicio, $fin, $festivo),
                ],
            );
        }
    }
}
