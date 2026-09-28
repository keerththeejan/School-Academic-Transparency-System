@extends('layouts.app')
@section('title', __('ui.daily_academic_summary'))
@section('content')
<x-page-header :title="__('ui.daily_academic_summary')" />
<p>{{ $summary->school->school_name }} · {{ $summary->schoolClass->label() }} · {{ $summary->date->translatedFormat('d F Y') }}</p>
@if($summary->day_note)<div class="alert alert-secondary">{{ $summary->day_note }}</div>@endif
<div class="panel table-responsive">
    <table class="table">
        <thead><tr><th>{{ __('ui.period') }}</th><th>{{ __('ui.subject') }}</th>@if($showTeachers)<th>{{ __('ui.teacher') }}</th>@endif<th>{{ __('ui.status') }}</th></tr></thead>
        <tbody>
        @foreach ($summary->items as $item)
            <tr>
                <td>{{ $item->period }}</td>
                <td>{{ $item->scheduled_subject }}</td>
                @if($showTeachers)<td>{{ $item->scheduled_teacher }}</td>@endif
                <td>{{ $lines[$item->id] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
