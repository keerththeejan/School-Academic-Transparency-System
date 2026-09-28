@extends('layouts.app')
@section('title', __('ui.leave'))
@section('content')
<x-page-header :title="__('ui.leave')" />
<form class="panel row g-2 mb-3" method="POST" action="{{ route('leave.store') }}">
    @csrf
    <div class="col-md-3"><select class="form-select" name="teacher_id" required>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>@endforeach</select></div>
    <div class="col-md-2"><select class="form-select" name="leave_type">@foreach(['approved_leave','official_duty','training','examination_duty','meeting','emergency_absence'] as $type)<option value="{{ $type }}">{{ __('ui.deviation_types.'.$type) }}</option>@endforeach</select></div>
    <div class="col-md-2"><input class="form-control" type="date" name="start_date" required></div>
    <div class="col-md-2"><input class="form-control" type="date" name="end_date" required></div>
    <div class="col-md-3"><input class="form-control" name="reason" placeholder="{{ __('ui.reason') }}"></div>
    <div class="col-12"><button class="btn btn-sats" type="submit">{{ __('ui.save') }}</button></div>
</form>
<div class="panel">
    @foreach ($records as $leave)
        <div class="d-flex justify-content-between border-bottom py-2">
            <div>@if($showTeachers){{ $leave->teacher->full_name }} · @endif{{ $leave->start_date->toDateString() }} – {{ $leave->end_date->toDateString() }} · {{ __('ui.'.$leave->status) }}</div>
            @if($leave->status === 'pending' && auth()->user()->hasPermission('leave.approve'))
                <form method="POST" action="{{ route('leave.approve', $leave) }}">@csrf<button class="btn btn-sm btn-sats" type="submit">{{ __('ui.approve') }}</button></form>
            @endif
        </div>
    @endforeach
    {{ $records->links() }}
</div>
@endsection
