@extends('layouts.base')
@section('body')
<main id="content" class="guest-wrap">
    <div class="guest-card">
        <p class="text-secondary mb-1">{{ __('ui.app_name') }}</p>
        <h1 class="h3 mb-2">{{ __('ui.app_full') }}</h1>
        <p class="text-secondary">{{ __('ui.tagline') }}</p>
        @include('partials.flash')
        @yield('content')
    </div>
</main>
@endsection
