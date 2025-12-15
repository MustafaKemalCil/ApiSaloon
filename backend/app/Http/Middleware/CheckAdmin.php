<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || $user->position!== 'Admin') {
            return abort(403, 'Bu sayfaya sadece admin erişebilir.');
        }

        return $next($request);
    }
}
