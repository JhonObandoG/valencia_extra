@extends('layouts.base')

@php
    // $pendientes llega del view composer de AppServiceProvider.
    $usuario = auth()->user();
@endphp

@section('cuerpo')
<div class="min-h-full">
    <aside id="sidebar" class="sidebar">
        <div class="flex items-center border-b border-white/10 px-5 py-5">
            <a href="{{ route('panel') }}">
                <x-logo />
            </a>
        </div>

        <nav class="sidebar-nav overflow-y-auto py-3">
            <p class="px-5 pb-2 pt-3 text-xs font-semibold uppercase tracking-wider text-white/40">
                Horas extra
            </p>
            @if ($usuario->esAdmin())
                <a class="sidebar-link {{ request()->routeIs('admin.aprobaciones') ? 'active' : '' }}" href="{{ route('admin.aprobaciones') }}">
                    <x-icono nombre="check" />
                    Aprobaciones
                    @if ($pendientes > 0)
                        <span class="ml-auto rounded-full bg-white/20 px-2 py-0.5 text-xs font-semibold">
                            {{ $pendientes }}
                        </span>
                    @endif
                </a>
            @endif
            <a class="sidebar-link {{ request()->routeIs('consulta') ? 'active' : '' }}" href="{{ route('consulta') }}">
                <x-icono nombre="buscar" />
                Consulta
            </a>

            @if ($usuario->esAdmin())
                <p class="px-5 pb-2 pt-5 text-xs font-semibold uppercase tracking-wider text-white/40">
                    Administración
                </p>
                <a class="sidebar-link {{ request()->routeIs('admin.empleados') ? 'active' : '' }}" href="{{ route('admin.empleados') }}">
                    <x-icono nombre="usuarios" />
                    Empleados
                </a>
            @endif

            <p class="px-5 pb-2 pt-5 text-xs font-semibold uppercase tracking-wider text-white/40">
                Público
            </p>
            <a class="sidebar-link" href="{{ route('registro') }}">
                <x-icono nombre="plus" />
                Formulario de registro
            </a>
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="mb-3 flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#d11e79] text-sm font-semibold text-white">
                    {{ mb_strtoupper(mb_substr($usuario->name, 0, 1)) }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-white">{{ $usuario->name }}</p>
                    <p class="text-xs capitalize text-white/50">{{ $usuario->rol }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full rounded-lg border border-white/20 px-3 py-2 text-sm text-white/80 transition-colors hover:bg-white/10 hover:text-white">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    <div id="sidebar-fondo" class="fixed inset-0 z-30 hidden bg-black/50 md:hidden"></div>

    <div class="md:pl-64">
        <header class="header">
            <div class="flex items-center gap-3 px-4 py-3 sm:px-6">
                <button type="button" id="abrir-sidebar"
                        class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 md:hidden" aria-label="Abrir menú">
                    <x-icono nombre="menu" class="h-5 w-5" />
                </button>
                <div class="min-w-0">
                    <h1 class="truncate text-lg font-semibold text-gray-800">@yield('titulo')</h1>
                    @hasSection('subtitulo')
                        <p class="truncate text-sm text-gray-500">@yield('subtitulo')</p>
                    @endif
                </div>
                <div class="ml-auto shrink-0">
                    @yield('cabecera')
                </div>
            </div>
        </header>

        <main class="p-4 sm:p-6 lg:p-8">
            @yield('contenido')
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Sidebar deslizante en pantallas pequeñas.
    (() => {
        const sidebar = document.getElementById('sidebar');
        const fondo = document.getElementById('sidebar-fondo');
        const abrir = document.getElementById('abrir-sidebar');
        if (!sidebar || !abrir) return;
        const alternar = (mostrar) => {
            sidebar.classList.toggle('open', mostrar);
            fondo.classList.toggle('hidden', !mostrar);
        };
        abrir.addEventListener('click', () => alternar(true));
        fondo.addEventListener('click', () => alternar(false));
        document.addEventListener('keydown', (e) => e.key === 'Escape' && alternar(false));
    })();
</script>
@endpush
