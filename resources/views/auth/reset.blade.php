@extends('layouts.guest')
@section('title', __('ui.reset_password'))
@section('content')
<form method="POST" action="{{ route('password.update') }}" class="mt-3">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="mb-3">
        <label class="form-label" for="email">{{ __('ui.email') }}</label>
        <input id="email" name="email" type="email" value="{{ old('email', $email) }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">{{ __('ui.new_password') }}</label>
        <input id="password" name="password" type="password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="password_confirmation">{{ __('ui.password_confirmation') }}</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
    </div>
    <button class="btn btn-sats" type="submit">{{ __('ui.reset_password') }}</button>
</form>
@endsection
