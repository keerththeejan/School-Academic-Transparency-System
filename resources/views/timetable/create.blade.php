@extends('layouts.app')
@section('title', __('ui.timetable'))
@section('content')
<x-page-header :title="__('ui.create')" />
<form class="panel" method="POST" action="{{ route('timetable.store') }}">
    @csrf
    <div class="mb-3"><label class="form-label" for="effective_from">{{ __('ui.effective_from') }}</label><input class="form-control" type="date" id="effective_from" name="effective_from" required></div>
    <div class="mb-3"><label class="form-label" for="effective_to">{{ __('ui.effective_to') }}</label><input class="form-control" type="date" id="effective_to" name="effective_to"></div>
    <button class="btn btn-sats" type="submit">{{ __('ui.save') }}</button>
</form>
@endsection
