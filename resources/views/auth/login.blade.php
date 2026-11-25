@extends('layouts.base')

@section('titulo', 'Ingresar')

@section('cuerpo')
<div class="flex min-h-full flex-col items-center justify-center bg-gradient-to-br from-[#2d001e] to-[#d11e79] px-4 py-8">
    <x-logo class="mb-8 h-auto w-56" />

    <div class="card animate-scale-in w-full max-w-sm">
        <h1 class="mb-6 text-2xl font-bold text-gray-900">Ingresar</h1>

        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="label">Correo electrónico</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                       autocomplete="username" class="input @error('email') input-error @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="label">Contraseña</label>
                <input type="password" name="password" id="password" required
                       autocomplete="current-password" class="input">
            </div>

            <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="remember" value="1"
                       class="rounded border-gray-300 text-[#d11e79] focus:ring-[#d11e79]">
                Recordarme
            </label>

            <button type="submit" class="btn btn-primary w-full py-2.5">Entrar</button>
        </form>
    </div>

    <p class="mt-6 text-center text-sm">
        <a href="{{ route('registro') }}" class="text-white/80 underline transition-colors hover:text-white">
            Registrar horas extra sin cuenta
        </a>
    </p>
</div>
@endsection
