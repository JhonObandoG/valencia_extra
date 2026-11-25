@extends('layouts.base')

@section('cuerpo')
<div class="flex min-h-full flex-col">

    <header class="bg-[#2d001e]">
        <div class="relative flex items-center justify-center px-4 py-4">
            <a href="{{ route('registro') }}">
                <x-logo />
            </a>
            @auth
                {{-- Fuera del flujo para que el logo siga centrado. --}}
                <a href="{{ route('panel') }}" style="position: absolute; right: 1.5rem"
                   class="text-sm text-white/70 transition-colors hover:text-white">
                    <span class="sm:hidden">Panel</span>
                    <span class="hidden sm:inline">Volver al panel</span>
                </a>
            @endauth
        </div>
    </header>

    <main class="mx-auto w-full max-w-2xl flex-1 px-4 py-8 sm:py-10">
        <x-avisos />

        @yield('contenido')
    </main>
</div>
@endsection
