<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckManager
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || !in_array($user->position, ['Manager', 'Admin'])) {
            return abort(403, 'Bu sayfaya sadece manager veya admin erişebilir.');
        }

        return $next($request);
    }
}
