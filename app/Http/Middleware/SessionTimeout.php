<?php

namespace App\Http\Middleware;

use App\Services\SettingService;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $minutes = (int) app(SettingService::class)->get(
                'session_timeout_minutes',
                config('sats.session_timeout'),
                $request->user()->school_id
            );
            $last = $request->session()->get('last_activity_at');

            if ($last && abs(Carbon::parse($last)->diffInMinutes(now())) >= $minutes) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['email' => __('ui.session_expired')]);
            }

            $request->session()->put('last_activity_at', now()->toIso8601String());
        }

        return $next($request);
    }
}
