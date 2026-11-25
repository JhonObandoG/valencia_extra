<?php

namespace App\Services;

use Carbon\CarbonImmutable;

/**
 * Reparte un turno en horas diurnas, nocturnas y dominicales, minuto a minuto.
 *
 * - Si la hora de fin es menor o igual que la de inicio, el turno termina al día siguiente.
 * - Un minuto en domingo, o en la fecha del turno si se marcó festivo, es dominical
 *   (tiene prioridad sobre nocturna). El festivo solo aplica al día de la fecha.
 * - Si no, es nocturno entre config('horas.nocturna_inicio') y config('horas.nocturna_fin').
 *
 * El formulario público tiene un espejo en JS para la vista previa; el que vale es este.
 */
class CalculadoraFranjas
{
    /**
     * @return array{horas_total: float, horas_diurnas: float, horas_nocturnas: float, horas_dominicales: float}
     */
    public function calcular(string $fecha, string $inicio, string $fin, bool $esFestivo = false): array
    {
        $desde = CarbonImmutable::parse($fecha.' '.substr($inicio, 0, 5));
        $hasta = CarbonImmutable::parse($fecha.' '.substr($fin, 0, 5));

        if ($hasta->lessThanOrEqualTo($desde)) {
            $hasta = $hasta->addDay();
        }

        $nocIni = $this->aMinutos(config('horas.nocturna_inicio'));
        $nocFin = $this->aMinutos(config('horas.nocturna_fin'));
        $dia = $desde->toDateString();

        $minutos = ['diurnas' => 0, 'nocturnas' => 0, 'dominicales' => 0];

        for ($t = $desde; $t->lessThan($hasta); $t = $t->addMinute()) {
            $minuto = $t->hour * 60 + $t->minute;

            if ($t->isSunday() || ($esFestivo && $t->toDateString() === $dia)) {
                $minutos['dominicales']++;
            } elseif ($this->esNocturno($minuto, $nocIni, $nocFin)) {
                $minutos['nocturnas']++;
            } else {
                $minutos['diurnas']++;
            }
        }

        return [
            'horas_total' => round(array_sum($minutos) / 60, 2),
            'horas_diurnas' => round($minutos['diurnas'] / 60, 2),
            'horas_nocturnas' => round($minutos['nocturnas'] / 60, 2),
            'horas_dominicales' => round($minutos['dominicales'] / 60, 2),
        ];
    }

    private function esNocturno(int $minuto, int $inicio, int $fin): bool
    {
        // Franja que cruza la medianoche (19:00-06:00) o que no.
        return $inicio > $fin
            ? ($minuto >= $inicio || $minuto < $fin)
            : ($minuto >= $inicio && $minuto < $fin);
    }

    private function aMinutos(string $hhmm): int
    {
        [$h, $m] = array_map('intval', explode(':', $hhmm));

        return $h * 60 + $m;
    }
}
