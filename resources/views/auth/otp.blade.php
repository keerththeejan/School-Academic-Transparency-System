@extends('layouts.guest')
@section('title', __('ui.otp_title'))
@section('content')
<form method="POST" action="{{ route('otp.store') }}" class="mt-3">
    @csrf
    <p>{{ __('ui.otp_help') }}</p>
    <label class="form-label" for="code">{{ __('ui.otp_title') }}</label>
    <input id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" class="form-control mb-3" required autofocus>
    <button class="btn btn-sats" type="submit">{{ __('ui.verify') }}</button>
</form>
@endsection
