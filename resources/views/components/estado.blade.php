@props(['estado'])

@php
    $clases = [
        'pendiente' => 'badge-pending',
        'aprobada' => 'badge-approved',
        'rechazada' => 'badge-rejected',
    ];
@endphp

<span class="badge {{ $clases[$estado] ?? 'badge-neutral' }}">{{ \App\Models\HoraExtra::ESTADOS[$estado] ?? $estado }}</span>
