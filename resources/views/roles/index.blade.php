@extends('layouts.app')
@section('content')
<x-page-header :title="__('ui.role_admin')" />
<div class="panel">
    @foreach ($roles as $role)
        <div class="d-flex justify-content-between border-bottom py-2">
            <div>{{ $role->name }} · {{ $role->permissions_count }}</div>
            <a href="{{ route('roles.edit', $role) }}">{{ __('ui.edit') }}</a>
        </div>
    @endforeach
</div>
@endsection
