@extends('layouts.base')
@section('body')
<main id="content" class="guest-wrap">
    <div class="guest-card">
        <p class="text-secondary">{{ __('ui.app_name') }}</p>
        <h1 class="h3">@yield('heading')</h1>
        <p>@yield('message')</p>
        <a class="btn btn-sats" href="{{ url('/') }}">{{ __('ui.dashboard') }}</a>
    </div>
</main>
@endsection
