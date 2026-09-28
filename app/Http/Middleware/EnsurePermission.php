<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasAnyPermission($permissions)) {
            Log::warning('authorization.denied', [
                'user_id' => $user?->id,
                'permissions' => $permissions,
                'path' => $request->path(),
            ]);
            abort(403);
        }

        return $next($request);
    }
}
