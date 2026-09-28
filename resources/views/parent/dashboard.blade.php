@extends('layouts.portal')
@section('title', __('ui.my_children'))
@section('content')
<h1 class="h4">{{ __('ui.my_children') }}</h1>
@forelse ($children as $child)
    <article class="panel mb-3">
        <h2 class="h5 mb-1">{{ $child->full_name }}</h2>
        <p class="mb-2 text-secondary">{{ $child->schoolClass?->label() }} · {{ $child->school?->school_name }}</p>
        @if ($summaries[$child->id] ?? null)
            <a class="btn btn-sats" href="{{ route('parent.summary', $summaries[$child->id]) }}">{{ __('ui.todays_summary') }}</a>
        @else
            <p class="mb-0">{{ __('ui.summary_pending') }}</p>
        @endif
    </article>
@empty
    <x-empty-state />
@endforelse
<a class="btn btn-outline-secondary" href="{{ route('parent.discrepancy.create') }}">{{ __('ui.report_discrepancy') }}</a>
@endsection
