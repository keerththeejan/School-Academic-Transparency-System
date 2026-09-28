@extends('layouts.portal')
@section('title', __('ui.previous_summaries'))
@section('content')
<h1 class="h4">{{ __('ui.previous_summaries') }}</h1>
<div class="panel">
    @forelse ($summaries as $summary)
        <div class="d-flex justify-content-between border-bottom py-2">
            <div>
                <strong>{{ $summary->schoolClass->label() }}</strong>
                <div class="small text-secondary">{{ $summary->date->toDateString() }} · {{ $summary->school->school_name }}</div>
            </div>
            <a href="{{ route('parent.summary', [$summary, 'student' => request('student')]) }}">{{ __('ui.view') }}</a>
        </div>
    @empty
        <x-empty-state />
    @endforelse
</div>
{{ $summaries->links() }}
@endsection
