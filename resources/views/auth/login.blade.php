@extends('layouts.guest')
@section('title', __('ui.login'))
@section('content')
<form method="POST" action="{{ route('login') }}" class="mt-3">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="email">{{ __('ui.email') }}</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" required autofocus autocomplete="username">
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">{{ __('ui.password') }}</label>
        <input id="password" name="password" type="password" class="form-control" required autocomplete="current-password">
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
        <label class="form-check-label" for="remember">{{ __('ui.remember_me') }}</label>
    </div>
    <button class="btn btn-sats w-100" type="submit">{{ __('ui.sign_in') }}</button>
    <p class="mt-3 mb-0"><a href="{{ route('password.request') }}">{{ __('ui.forgot_password') }}</a></p>
</form>
@if (app()->environment('local'))
    <div class="mt-4 small text-secondary">
        <div class="fw-semibold">{{ __('ui.demo_title') }}</div>
        <div>{{ __('ui.demo_password') }}</div>
        <div>principal@sats.test · teacher@sats.test · parent@sats.test · sdc@sats.test</div>
    </div>
@endif
@endsection
