@extends('layouts.app')
@section('title', __('ui.deviations'))
@section('content')
<x-page-header :title="__('ui.report_deviation')" />
<form class="panel mb-3" method="GET">
    <div class="row g-2">
        <div class="col-md-4"><label class="form-label" for="class_id">{{ __('ui.class') }}</label><select id="class_id" class="form-select" name="class_id">@foreach($classes as $class)<option value="{{ $class->id }}" @selected(request('class_id')==$class->id)>{{ $class->label() }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label" for="date">{{ __('ui.date') }}</label><input id="date" class="form-control" type="date" name="date" value="{{ request('date', now()->toDateString()) }}"></div>
        <div class="col-md-2 d-flex align-items-end"><button class="btn btn-outline-secondary" type="submit">{{ __('ui.filter') }}</button></div>
    </div>
</form>
@foreach ($slots as $slot)
    <form class="panel mb-3" method="POST" action="{{ route('deviations.store') }}">
        @csrf
        <input type="hidden" name="daily_schedule_id" value="{{ $slot->id }}">
        <h2 class="h6">{{ $slot->schoolClass->label() }} · {{ __('ui.period') }} {{ $slot->period }} · {{ $slot->subject?->subject_name }}</h2>
        @include('partials.deviation-fields')
        <button class="btn btn-sats" type="submit">{{ __('ui.submit') }}</button>
    </form>
@endforeach
@endsection
