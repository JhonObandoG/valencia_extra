<?php

return [

    /*
    | Franja nocturna. Si el fin es menor que el inicio, la franja cruza la medianoche.
    */
    'nocturna_inicio' => env('HORAS_NOCTURNA_INICIO', '19:00'),
    'nocturna_fin' => env('HORAS_NOCTURNA_FIN', '06:00'),

    /*
    | Turnos que se pueden enviar de una sola vez en el formulario público.
    */
    'max_turnos' => 20,

];
