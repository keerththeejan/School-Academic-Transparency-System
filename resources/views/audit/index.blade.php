@extends('layouts.app')
@section('title', __('ui.audit_logs'))
@section('content')
<x-page-header :title="__('ui.audit_logs')" />
<p>{{ __('ui.audit_intro') }}</p>
<form class="panel row g-2 mb-3" method="GET">
    <div class="col-md-3"><input class="form-control" name="action" value="{{ request('action') }}" placeholder="{{ __('ui.actions') }}"></div>
    <div class="col-md-3"><input class="form-control" name="record_type" value="{{ request('record_type') }}" placeholder="{{ __('ui.record') }}"></div>
    <div class="col-md-2"><button class="btn btn-sats" type="submit">{{ __('ui.filter') }}</button></div>
</form>
<div class="panel">
    @foreach ($records as $log)
        <article class="border-bottom py-3">
            <div class="fw-semibold">{{ $log->timestamp }} · {{ $log->action }} · {{ $log->record_type }} #{{ $log->record_id }}</div>
            <div class="small text-secondary">{{ $log->user?->name }} · {{ $log->ip_address }}</div>
            @if ($log->reason)<div>{{ $log->reason }}</div>@endif
            @if ($log->old_value)<div class="small"><strong>{{ __('ui.old_value') }}:</strong> {{ json_encode($log->old_value) }}</div>@endif
            @if ($log->new_value)<div class="small"><strong>{{ __('ui.new_value') }}:</strong> {{ json_encode($log->new_value) }}</div>@endif
        </article>
    @endforeach
    {{ $records->links() }}
</div>
@endsection
