<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HoraExtra;
use Illuminate\Http\Request;

class AprobacionController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->only(['desde', 'hasta', 'franja', 'estado', 'empleado']);
        // Por defecto se ve lo que falta revisar.
        $filtros['estado'] ??= HoraExtra::PENDIENTE;

        $registros = HoraExtra::query()->filtrar($filtros)
            ->with(['empleado', 'revisor'])
            ->orderBy('fecha')->orderBy('hora_inicio')
            ->paginate(25)->withQueryString();

        return view('admin.aprobaciones', compact('filtros', 'registros'));
    }

    public function aprobar(Request $request, HoraExtra $horaExtra)
    {
        abort_unless($horaExtra->estado === HoraExtra::PENDIENTE, 422, 'Este registro ya fue revisado.');

        $horaExtra->update([
            'estado' => HoraExtra::APROBADA,
            'revisado_por' => $request->user()->id,
            'revisado_at' => now(),
            'motivo_rechazo' => null,
        ]);

        return back()->with('exito', "Aprobado el turno de {$horaExtra->empleado->nombre}.");
    }

    public function rechazar(Request $request, HoraExtra $horaExtra)
    {
        abort_unless($horaExtra->estado === HoraExtra::PENDIENTE, 422, 'Este registro ya fue revisado.');

        $datos = $request->validate([
            'motivo_rechazo' => ['required', 'string', 'max:1000'],
        ], [], ['motivo_rechazo' => 'motivo del rechazo']);

        $horaExtra->update([
            'estado' => HoraExtra::RECHAZADA,
            'revisado_por' => $request->user()->id,
            'revisado_at' => now(),
            'motivo_rechazo' => $datos['motivo_rechazo'],
        ]);

        return back()->with('exito', "Rechazado el turno de {$horaExtra->empleado->nombre}.");
    }
}
