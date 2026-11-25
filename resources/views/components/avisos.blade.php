{{-- Mensaje de éxito (flash) y primer error de validación. --}}
@if (session('exito'))
    <div class="animate-slide-up mb-6 flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        <x-icono nombre="check" class="mt-0.5 h-4 w-4 text-green-600" />
        <span>{{ session('exito') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="animate-slide-up mb-6 flex items-start gap-2 rounded-lg border-2 border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <x-icono nombre="alerta" class="mt-0.5 h-4 w-4 shrink-0" />
        <span>{{ $errors->first() }}</span>
    </div>
@endif
