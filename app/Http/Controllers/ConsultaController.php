<?php

namespace App\Http\Controllers;

use App\Models\HoraExtra;
use Illuminate\Http\Request;

class ConsultaController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->only(['desde', 'hasta', 'franja', 'estado', 'horas_min', 'horas_max', 'empleado']);

        $base = HoraExtra::query()->filtrar($filtros);

        $totales = (clone $base)->selectRaw(
            'COALESCE(SUM(horas_total),0) total, COALESCE(SUM(horas_diurnas),0) diurnas,
             COALESCE(SUM(horas_nocturnas),0) nocturnas, COALESCE(SUM(horas_dominicales),0) dominicales'
        )->first();

        $registros = $base->with(['empleado', 'revisor'])
            ->orderByDesc('fecha')->orderByDesc('hora_inicio')
            ->paginate(25)->withQueryString();

        return view('consulta', compact('filtros', 'totales', 'registros'));
    }
}
