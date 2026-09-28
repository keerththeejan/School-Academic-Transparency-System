@extends('layouts.app')
@section('title', __('ui.report_deviation'))
@section('content')
<x-page-header :title="__('ui.report_deviation')" />
<div class="panel mb-3">
    <div>{{ $slot->schoolClass->label() }} · {{ __('ui.period') }} {{ $slot->period }}</div>
    <div>{{ $slot->subject?->subject_name }}</div>
</div>
<form class="panel" method="POST" action="{{ route('deviations.store') }}">
    @csrf
    <input type="hidden" name="daily_schedule_id" value="{{ $slot->id }}">
    @include('partials.deviation-fields')
    <button class="btn btn-sats" type="submit">{{ __('ui.submit') }}</button>
</form>
@endsection
