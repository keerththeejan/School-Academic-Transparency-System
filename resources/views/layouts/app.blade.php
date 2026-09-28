@extends('layouts.base')
@section('body')
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <strong>{{ __('ui.app_name') }}</strong>
            <span>{{ auth()->user()->school->school_name ?? __('ui.app_full') }}</span>
        </div>
        <ul class="side-nav">
            @foreach ($navItems ?? [] as $item)
                <li>
                    <a href="{{ route($item['route']) }}" @if (request()->routeIs($item['route'])) class="active" aria-current="page" @endif>
                        {{ __($item['label']) }}
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>
    <div class="workspace">
        <header class="topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary menu-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav">{{ __('ui.menu') }}</button>
                <div>
                    <div class="fw-semibold">{{ auth()->user()->name }}</div>
                    <div class="text-secondary small">{{ __('ui.roles.'.auth()->user()->primaryRole()) }}</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('notifications.index') }}" class="text-decoration-none">{{ __('ui.notifications') }} @if(($unreadCount ?? 0) > 0)<span class="badge-soft">{{ $unreadCount }}</span>@endif</a>
                <a href="{{ route('profile.edit') }}">{{ __('ui.profile') }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary" type="submit">{{ __('ui.logout') }}</button></form>
            </div>
        </header>
        <main id="content" class="page">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="mobileNav" style="background:#0c2d3e;color:#fff;width:280px">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title h5">{{ __('ui.app_name') }}</h2>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="{{ __('ui.close') }}"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="side-nav">
            @foreach ($navItems ?? [] as $item)
                <li><a href="{{ route($item['route']) }}">{{ __($item['label']) }}</a></li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
