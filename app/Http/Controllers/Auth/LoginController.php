<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditLogger;
use App\Support\Totp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request, AuditLogger $audit)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = 'login-lock:'.mb_strtolower($data['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            return back()->withErrors(['email' => __('ui.too_many_attempts')])->onlyInput('email');
        }

        $user = User::query()->where('email', $data['email'])->first();
        $ok = Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $request->boolean('remember'));

        LoginAttempt::query()->create([
            'email' => $data['email'],
            'user_id' => $user?->id,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 2000),
            'successful' => $ok && $user?->isActive(),
            'created_at' => now(),
        ]);

        if (! $ok || ! $user || ! $user->isActive()) {
            Auth::logout();
            RateLimiter::hit($key, 900);
            Log::warning('auth.failed', ['email' => $data['email'], 'ip' => $request->ip()]);

            return back()->withErrors(['email' => __('ui.invalid_credentials')])->onlyInput('email');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        if ($user->otp_enabled) {
            Auth::logout();
            $request->session()->put('otp_user_id', $user->id);

            return redirect()->route('otp.create');
        }

        $this->completeLogin($request, $user, $audit);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request, AuditLogger $audit)
    {
        $user = $request->user();
        if ($user) {
            UserSession::query()->where('user_id', $user->id)->where('session_id', $request->session()->getId())->update(['logged_out_at' => now()]);
            $audit->log($user, 'LOGOUT', 'user', $user->id, null, null, null, $user->school_id);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function otpForm()
    {
        abort_unless(session()->has('otp_user_id'), 403);

        return view('auth.otp');
    }

    public function otpStore(Request $request, AuditLogger $audit)
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $user = User::query()->findOrFail(session('otp_user_id'));
        if (! $user->otp_secret || ! Totp::verify($user->otp_secret, $data['code'])) {
            return back()->withErrors(['code' => __('ui.invalid_credentials')]);
        }

        Auth::login($user);
        $request->session()->forget('otp_user_id');
        $request->session()->regenerate();
        $this->completeLogin($request, $user, $audit);

        return redirect()->route('dashboard');
    }

    private function completeLogin(Request $request, User $user, AuditLogger $audit): void
    {
        $user->forceFill(['last_login_at' => now()])->save();
        UserSession::query()->create([
            'user_id' => $user->id,
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 2000),
            'last_activity' => now(),
        ]);
        $audit->log($user, 'LOGIN', 'user', $user->id, null, ['ip' => $request->ip()], null, $user->school_id);
        Log::info('auth.login', ['user_id' => $user->id]);
    }
}
