@extends('layouts.guest')
@section('title', __('ui.forgot_password'))
@section('content')
<form method="POST" action="{{ route('password.email') }}" class="mt-3">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="email">{{ __('ui.email') }}</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" required>
    </div>
    <button class="btn btn-sats" type="submit">{{ __('ui.send_reset') }}</button>
</form>
@endsection
