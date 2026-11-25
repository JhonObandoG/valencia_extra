<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\HoraExtra;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmpleadoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $empleados = Empleado::query()
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('nombre', 'like', "%{$q}%")
                ->orWhere('documento', 'like', "%{$q}%")))
            ->withSum(['horasExtra as horas_aprobadas' => fn ($h) => $h->where('estado', HoraExtra::APROBADA)], 'horas_total')
            ->withCount(['horasExtra as pendientes' => fn ($h) => $h->where('estado', HoraExtra::PENDIENTE)])
            ->orderBy('nombre')
            ->paginate(25)->withQueryString();

        return view('admin.empleados', [
            'empleados' => $empleados,
            'q' => $q,
            'total' => Empleado::count(),
        ]);
    }

    public function update(Request $request, Empleado $empleado)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'documento' => ['required', 'string', 'max:30', Rule::unique('empleados', 'documento')->ignore($empleado->id)],
        ], [
            'documento.unique' => 'Ese documento ya está registrado.',
        ]);

        $empleado->update([
            'nombre' => trim($datos['nombre']),
            'documento' => trim($datos['documento']),
            'activo' => $request->boolean('activo'),
        ]);

        return back()->with('exito', "Se guardaron los cambios de {$empleado->nombre}.");
    }
}
