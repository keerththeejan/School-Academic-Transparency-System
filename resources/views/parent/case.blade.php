@extends('layouts.portal')
@section('title', __('ui.discrepancies'))
@section('content')
<h1 class="h4">{{ __('ui.your_report') }}</h1>
<div class="panel">
    <p>{{ $case->feedback->date->toDateString() }} · {{ __('ui.period') }} {{ $case->feedback->period }}</p>
    <p>{{ $case->feedback->description }}</p>
    <p><span class="badge-soft">{{ __('ui.'.$case->status) }}</span></p>
    @if ($case->resolution)
        <h2 class="h6">{{ __('ui.school_response') }}</h2>
        <p>{{ __('ui.classifications.'.$case->classification) }}</p>
        <p>{{ $case->resolution }}</p>
    @else
        <p>{{ __('ui.awaiting_review') }}</p>
    @endif
</div>
@endsection
