<?php

// Solo las reglas que usa la app.
return [
    'array' => 'El campo :attribute no es válido.',
    'before_or_equal' => 'La :attribute no puede ser posterior a hoy.',
    'boolean' => 'El campo :attribute no es válido.',
    'date' => 'La :attribute no es una fecha válida.',
    'date_format' => 'La :attribute debe tener el formato HH:MM.',
    'different' => 'La :attribute debe ser distinta de la :other.',
    'email' => 'Escribe un correo electrónico válido.',
    'max' => [
        'array' => 'Puedes enviar como máximo :max turnos a la vez.',
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'min' => [
        'array' => 'Agrega al menos :min turno.',
    ],
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute no es válido.',
    'unique' => 'Ese :attribute ya está registrado.',

    'attributes' => [
        'documento' => 'documento',
        'nombre' => 'nombre',
        'email' => 'correo',
        'password' => 'contraseña',
        'turnos' => 'turnos',
        'turnos.*.hora_inicio' => 'hora de inicio',
    ],
];
