<?php

if (! function_exists('horas')) {
    /** Horas sin ceros de sobra: 4.00 -> "4", 2.50 -> "2.5", 7.25 -> "7.25". */
    function horas(float|int|string|null $valor): string
    {
        return (string) (float) round((float) $valor, 2);
    }
}
