@extends('layouts.app')
@section('title', __('ui.discrepancies'))
@section('content')
<x-page-header :title="__('ui.discrepancies')" />
<div class="panel">
    <p>{{ $case->feedback->student->full_name }} · {{ $case->feedback->student->schoolClass?->label() }}</p>
    <p>{{ $case->feedback->date->toDateString() }} · {{ __('ui.period') }} {{ $case->feedback->period }}</p>
    <p>{{ $case->feedback->description }}</p>
</div>
<div class="panel mt-3">
    <h2 class="h6">{{ __('ui.audit_logs') }}</h2>
    @foreach ($case->actions as $action)
        <div class="border-bottom py-2"><strong>{{ $action->action }}</strong> · {{ $action->user?->name }} · {{ $action->created_at }}<div>{{ $action->notes }}</div></div>
    @endforeach
</div>
<form class="panel mt-3" method="POST" action="{{ route('discrepancies.resolve', $case) }}">
    @csrf
    <label class="form-label" for="classification">{{ __('ui.classification') }}</label>
    <select class="form-select mb-3" id="classification" name="classification" required>
        @foreach (\App\Support\Classifications::all() as $item)
            <option value="{{ $item }}">{{ __('ui.classifications.'.$item) }}</option>
        @endforeach
    </select>
    <label class="form-label" for="resolution">{{ __('ui.resolution') }}</label>
    <p class="small text-secondary">{{ __('ui.resolution_help') }}</p>
    <textarea class="form-control mb-3" id="resolution" name="resolution" rows="4" required></textarea>
    <button class="btn btn-sats" type="submit">{{ __('ui.close') }}</button>
</form>
@endsection
