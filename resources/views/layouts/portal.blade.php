@extends('layouts.base')
@section('body')
<header class="portal-top">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <div class="small">{{ __('ui.app_name') }}</div>
            <strong>{{ auth()->user()->name }}</strong>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light" type="submit">{{ __('ui.logout') }}</button></form>
    </div>
</header>
<main id="content" class="portal-body">
    @include('partials.flash')
    @yield('content')
</main>
<nav class="bottom-nav" aria-label="{{ __('ui.menu') }}">
    <a href="{{ route('parent.dashboard') }}">{{ __('ui.my_children') }}</a>
    <a href="{{ route('parent.summaries') }}">{{ __('ui.previous_summaries') }}</a>
    <a href="{{ route('parent.notifications') }}">{{ __('ui.notifications') }}</a>
    <a href="{{ route('profile.edit') }}">{{ __('ui.profile') }}</a>
</nav>
@endsection
