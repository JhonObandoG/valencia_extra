<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HoraExtra extends Model
{
    public const PENDIENTE = 'pendiente';

    public const APROBADA = 'aprobada';

    public const RECHAZADA = 'rechazada';

    public const ESTADOS = [
        self::PENDIENTE => 'Pendiente',
        self::APROBADA => 'Aprobada',
        self::RECHAZADA => 'Rechazada',
    ];

    /** Filtro de franja => columna con las horas de esa franja. */
    public const FRANJAS = [
        'diurna' => 'horas_diurnas',
        'nocturna' => 'horas_nocturnas',
        'dominical' => 'horas_dominicales',
    ];

    protected $table = 'horas_extra';

    protected $fillable = [
        'empleado_id', 'fecha', 'hora_inicio', 'hora_fin', 'es_festivo',
        'horas_total', 'horas_diurnas', 'horas_nocturnas', 'horas_dominicales',
        'observacion', 'estado', 'revisado_por', 'revisado_at', 'motivo_rechazo',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'es_festivo' => 'boolean',
            'horas_total' => 'float',
            'horas_diurnas' => 'float',
            'horas_nocturnas' => 'float',
            'horas_dominicales' => 'float',
            'revisado_at' => 'datetime',
        ];
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    /** HH:MM sin segundos. */
    public function inicio(): string
    {
        return substr($this->hora_inicio, 0, 5);
    }

    public function fin(): string
    {
        return substr($this->hora_fin, 0, 5);
    }

    public function cruzaMedianoche(): bool
    {
        return $this->hora_fin <= $this->hora_inicio;
    }

    /** "05/09/2026 · 19:00 - 23:00 · 4 h", para el modal de rechazo. */
    public function detalle(): string
    {
        return $this->fecha->format('d/m/Y').' · '.$this->inicio().' - '.$this->fin().' · '.horas($this->horas_total).' h';
    }

    /** Filtros comunes de Consulta y Aprobaciones. */
    public function scopeFiltrar(Builder $query, array $f): Builder
    {
        $franja = self::FRANJAS[$f['franja'] ?? ''] ?? null;
        $estado = isset(self::ESTADOS[$f['estado'] ?? '']) ? $f['estado'] : null;
        $empleado = trim($f['empleado'] ?? '');

        return $query
            ->when($f['desde'] ?? null, fn ($q, $v) => $q->whereDate('fecha', '>=', $v))
            ->when($f['hasta'] ?? null, fn ($q, $v) => $q->whereDate('fecha', '<=', $v))
            ->when($franja, fn ($q) => $q->where($franja, '>', 0))
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when(is_numeric($f['horas_min'] ?? null), fn ($q) => $q->where('horas_total', '>=', $f['horas_min']))
            ->when(is_numeric($f['horas_max'] ?? null), fn ($q) => $q->where('horas_total', '<=', $f['horas_max']))
            ->when($empleado !== '', fn ($q) => $q->whereHas('empleado', fn ($e) => $e
                ->where('nombre', 'like', "%{$empleado}%")
                ->orWhere('documento', 'like', "%{$empleado}%")));
    }
}
