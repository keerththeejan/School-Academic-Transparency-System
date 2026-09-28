@extends('layouts.app')
@section('title', __('ui.timetable'))
@section('content')
<x-page-header :title="__('ui.timetable')" :action="route('timetable.create')" :action-label="__('ui.create')" />
<p class="text-secondary">{{ __('ui.history_kept') }}</p>
@foreach ($versions as $version)
    <section class="panel mb-3">
        <div class="d-flex justify-content-between flex-wrap gap-2">
            <div>
                <h2 class="h5 mb-1">{{ __('ui.version') }} {{ $version->version_number }}</h2>
                <div class="small text-secondary">{{ $version->effective_from->toDateString() }} — {{ $version->effective_to?->toDateString() ?? '…' }} · {{ __('ui.'.$version->status) }}</div>
            </div>
            <div class="d-flex gap-2">
                @if ($version->status === 'draft')
                    <form method="POST" action="{{ route('timetable.approve', $version) }}">@csrf<button class="btn btn-outline-secondary btn-sm" type="submit">{{ __('ui.approve_timetable') }}</button></form>
                @endif
                @if (in_array($version->status, ['approved', 'published']))
                    <form method="POST" action="{{ route('timetable.publish', $version) }}">@csrf<button class="btn btn-sats btn-sm" type="submit">{{ __('ui.publish_timetable') }}</button></form>
                @endif
            </div>
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-sm">
                <thead><tr><th>{{ __('ui.class') }}</th><th>{{ __('ui.day') }}</th><th>{{ __('ui.period') }}</th><th>{{ __('ui.subject') }}</th>@if($showTeachers)<th>{{ __('ui.teacher') }}</th>@endif</tr></thead>
                <tbody>
                @foreach ($version->entries as $entry)
                    <tr>
                        <td>{{ $entry->schoolClass->label() }}</td>
                        <td>{{ __('ui.weekdays.'.$entry->day_of_week) }}</td>
                        <td>{{ $entry->period }}</td>
                        <td>{{ $entry->subject->subject_name }}</td>
                        @if($showTeachers)<td>{{ $entry->teacher?->full_name }}</td>@endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if ($version->status !== 'archived')
            <form class="row g-2" method="POST" action="{{ route('timetable.entries.store', $version) }}">
                @csrf
                <div class="col-md-3"><select class="form-select" name="class_id" required>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->label() }}</option>@endforeach</select></div>
                <div class="col-md-2"><select class="form-select" name="day_of_week" required>@foreach(__('ui.weekdays') as $num => $label)<option value="{{ $num }}">{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-1"><input class="form-control" name="period" type="number" min="1" max="12" placeholder="{{ __('ui.period') }}" required></div>
                <div class="col-md-2"><select class="form-select" name="subject_id" required>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>@endforeach</select></div>
                <div class="col-md-2"><select class="form-select" name="teacher_id"><option value="">{{ __('ui.teacher') }}</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>@endforeach</select></div>
                <div class="col-md-2"><button class="btn btn-outline-secondary w-100" type="submit">{{ __('ui.add_period') }}</button></div>
            </form>
        @endif
    </section>
@endforeach
@endsection
