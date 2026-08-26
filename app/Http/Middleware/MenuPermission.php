<?php

namespace App\Http\Middleware;

use App\Models\MenuItem;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $menu = MenuItem::where('route', $request->route()->getName())->where('is_active', true)->first();
        if ($menu && !MenuItem::visibleTo($request->user())->whereKey($menu->id)->exists()) abort(403);
        return $next($request);
    }
}