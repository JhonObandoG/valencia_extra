<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /** Uso: ->middleware('rol:admin') o 'rol:admin,consultor'. */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_unless(in_array($request->user()?->rol, $roles, true), 403);

        return $next($request);
    }
}
