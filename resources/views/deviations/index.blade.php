@extends('layouts.app')
@section('title', __('ui.deviations'))
@section('content')
<x-page-header :title="__('ui.deviations')" :action="route('deviations.create')" :action-label="__('ui.create')" />
<form class="panel row g-2 mb-3" method="GET">
    <div class="col-md-3"><label class="form-label" for="date">{{ __('ui.date') }}</label><input id="date" class="form-control" type="date" name="date" value="{{ request('date') }}"></div>
    <div class="col-md-3"><label class="form-label" for="status">{{ __('ui.status') }}</label><select id="status" class="form-select" name="status"><option value="">{{ __('ui.all') }}</option>@foreach(['pending','approved','rejected'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ __('ui.'.$status) }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label" for="deviation_type">{{ __('ui.type') }}</label><select id="deviation_type" class="form-select" name="deviation_type"><option value="">{{ __('ui.all') }}</option>@foreach(\App\Support\DeviationCatalog::keys() as $key)<option value="{{ $key }}" @selected(request('deviation_type')===$key)>{{ __('ui.deviation_types.'.$key) }}</option>@endforeach</select></div>
    <div class="col-md-2 d-flex align-items-end"><button class="btn btn-sats" type="submit">{{ __('ui.filter') }}</button></div>
</form>
<div class="panel table-responsive">
    <table class="table">
        <thead><tr><th>{{ __('ui.date') }}</th><th>{{ __('ui.class') }}</th><th>{{ __('ui.period') }}</th><th>{{ __('ui.subject') }}</th>@if($showTeachers)<th>{{ __('ui.teacher') }}</th>@endif<th>{{ __('ui.status') }}</th><th></th></tr></thead>
        <tbody>
        @forelse ($records as $row)
            <tr>
                <td>{{ $row->date->toDateString() }}</td>
                <td>{{ $row->schoolClass->label() }}</td>
                <td>{{ $row->period }}</td>
                <td>{{ $row->scheduled_subject }}</td>
                @if($showTeachers)<td>{{ $row->scheduled_teacher }}</td>@endif
                <td><span class="badge-soft">{{ __('ui.deviation_text.'.$row->deviation_type) }}</span></td>
                <td><a href="{{ route('deviations.show', $row) }}">{{ __('ui.view') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="7"><x-empty-state /></td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $records->links() }}
</div>
@endsection
