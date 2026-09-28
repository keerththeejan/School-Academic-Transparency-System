@extends('layouts.app')
@section('title', __('ui.deviations'))
@section('content')
<x-page-header :title="__('ui.details')" />
<div class="panel">
    <dl class="row mb-0">
        <dt class="col-sm-3">{{ __('ui.date') }}</dt><dd class="col-sm-9">{{ $deviation->date->toDateString() }}</dd>
        <dt class="col-sm-3">{{ __('ui.class') }}</dt><dd class="col-sm-9">{{ $deviation->schoolClass->label() }}</dd>
        <dt class="col-sm-3">{{ __('ui.period') }}</dt><dd class="col-sm-9">{{ $deviation->period }}</dd>
        <dt class="col-sm-3">{{ __('ui.subject') }}</dt><dd class="col-sm-9">{{ $deviation->scheduled_subject }}</dd>
        @if($showTeachers)
            <dt class="col-sm-3">{{ __('ui.scheduled_teacher') }}</dt><dd class="col-sm-9">{{ $deviation->scheduled_teacher }}</dd>
            <dt class="col-sm-3">{{ __('ui.relief_teacher') }}</dt><dd class="col-sm-9">{{ $deviation->reliefTeacher?->full_name ?? __('ui.not_covered') }}</dd>
        @endif
        <dt class="col-sm-3">{{ __('ui.status') }}</dt><dd class="col-sm-9">{{ __('ui.deviation_text.'.$deviation->deviation_type) }} · {{ __('ui.'.$deviation->status) }}</dd>
        <dt class="col-sm-3">{{ __('ui.description') }}</dt><dd class="col-sm-9">{{ $deviation->description }}</dd>
    </dl>
</div>
@if(auth()->user()->hasPermission('deviations.approve') && $deviation->status === 'pending')
    <div class="d-flex gap-2 mt-3">
        <form method="POST" action="{{ route('deviations.approve', $deviation) }}">@csrf<button class="btn btn-sats" type="submit">{{ __('ui.approve') }}</button></form>
        <form method="POST" action="{{ route('deviations.reject', $deviation) }}" class="d-flex gap-2">@csrf<input class="form-control" name="reason" placeholder="{{ __('ui.reason') }}" required><button class="btn btn-outline-danger" type="submit">{{ __('ui.reject') }}</button></form>
    </div>
@endif
@if(auth()->user()->hasPermission('deviations.approve'))
    <form class="panel mt-3" method="POST" action="{{ route('deviations.correct', $deviation) }}">
        @csrf
        <h2 class="h6">{{ __('ui.correct') }}</h2>
        <p class="small text-secondary">{{ __('ui.original_remains') }}</p>
        <div class="mb-3"><label class="form-label" for="correction_reason">{{ __('ui.correction_reason') }}</label><input class="form-control" id="correction_reason" name="correction_reason" required></div>
        <div class="mb-3"><label class="form-label" for="description">{{ __('ui.description') }}</label><textarea class="form-control" id="description" name="description" rows="3">{{ $deviation->description }}</textarea></div>
        <div class="mb-3"><label class="form-label" for="action_taken">{{ __('ui.action_taken') }}</label><input class="form-control" id="action_taken" name="action_taken" value="{{ $deviation->action_taken }}"></div>
        <button class="btn btn-outline-secondary" type="submit">{{ __('ui.save') }}</button>
    </form>
@endif
@if($audits->isNotEmpty())
    <div class="panel mt-3">
        <h2 class="h6">{{ __('ui.audit_logs') }}</h2>
        @foreach ($audits as $audit)
            <article class="border-bottom py-2">
                <div>{{ $audit->timestamp }} · {{ $audit->action }} · {{ $audit->user?->name }}</div>
                <div class="small">{{ $audit->reason }}</div>
            </article>
        @endforeach
    </div>
@endif
@endsection
