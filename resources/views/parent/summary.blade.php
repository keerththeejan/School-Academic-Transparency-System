@extends('layouts.portal')
@section('title', __('ui.daily_academic_summary'))
@section('content')
<p class="mb-1">{{ $summary->school->school_name }}</p>
<h1 class="h4">{{ __('ui.daily_academic_summary') }}</h1>
<p>{{ $student->full_name }} · {{ $summary->schoolClass->label() }} · {{ $summary->date->translatedFormat('d F Y') }}</p>
@if ($summary->day_note)
    <div class="alert alert-secondary">{{ __('ui.holiday_note') }}: {{ $summary->day_note }}</div>
@endif
<div class="panel">
    <table class="table mb-0">
        <caption class="visually-hidden">{{ __('ui.daily_academic_summary') }}</caption>
        <thead><tr><th>{{ __('ui.period') }}</th><th>{{ __('ui.subject') }}</th><th>{{ __('ui.status') }}</th></tr></thead>
        <tbody>
        @forelse ($summary->items as $item)
            <tr>
                <td>{{ $item->period }}</td>
                <td>{{ $item->scheduled_subject }}</td>
                <td>{{ $lines[$item->id] }}</td>
            </tr>
        @empty
            <tr><td colspan="3">{{ $summary->day_note ?: __('ui.empty_body') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<p class="mt-3">{{ __('ui.discrepancy_prompt') }}</p>
<div class="d-flex gap-2">
    <a class="btn btn-sats" href="{{ route('parent.discrepancy.create', ['student_id' => $student->id, 'date' => $summary->date->toDateString()]) }}">{{ __('ui.yes') }}</a>
    <a class="btn btn-outline-secondary" href="{{ route('parent.dashboard') }}">{{ __('ui.no') }}</a>
</div>
@endsection
