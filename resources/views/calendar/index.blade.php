@extends('layouts.app')
@section('title', __('ui.calendar'))
@section('content')
<x-page-header :title="__('ui.calendar')" />
<form class="panel row g-2 mb-3" method="POST" action="{{ route('calendar.store') }}">
    @csrf
    <div class="col-md-2"><input class="form-control" type="date" name="date" required></div>
    <div class="col-md-3"><select class="form-select" name="calendar_type" required>@foreach(\App\Support\CalendarTypes::all() as $type)<option value="{{ $type }}">{{ __('ui.calendar_types.'.$type) }}</option>@endforeach</select></div>
    <div class="col-md-3"><input class="form-control" name="title" placeholder="{{ __('ui.title') }}" required></div>
    <div class="col-md-2"><select class="form-select" name="is_school_day"><option value="0">{{ __('ui.no') }}</option><option value="1">{{ __('ui.school_day') }}</option></select></div>
    <div class="col-md-2"><select class="form-select" name="substitutes_day_of_week"><option value="">{{ __('ui.substitutes_day') }}</option>@foreach(__('ui.weekdays') as $n => $label)<option value="{{ $n }}">{{ $label }}</option>@endforeach</select></div>
    <div class="col-12"><button class="btn btn-sats" type="submit">{{ __('ui.save') }}</button></div>
</form>
<div class="panel">
    @foreach ($records as $row)
        <div class="border-bottom py-2">{{ $row->date->toDateString() }} · {{ __('ui.calendar_types.'.$row->calendar_type) }} · {{ $row->title }} · {{ $row->is_school_day ? __('ui.school_day') : __('ui.holiday_note') }}</div>
    @endforeach
    {{ $records->links() }}
</div>
@endsection
