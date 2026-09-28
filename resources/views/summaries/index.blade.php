@extends('layouts.app')
@section('title', __('ui.summaries'))
@section('content')
<x-page-header :title="__('ui.summaries')" />
<form class="d-flex gap-2 mb-3" method="POST" action="{{ route('summaries.generate') }}">
    @csrf
    <input class="form-control" type="date" name="date" value="{{ request('date', now()->toDateString()) }}">
    <button class="btn btn-sats" type="submit">{{ __('ui.generate') }}</button>
</form>
<div class="panel">
    @forelse ($records as $summary)
        <div class="d-flex justify-content-between border-bottom py-2">
            <div>{{ $summary->date->toDateString() }} · {{ $summary->schoolClass->label() }} · {{ __('ui.'.$summary->status) }}</div>
            <a href="{{ route('summaries.show', $summary) }}">{{ __('ui.view') }}</a>
        </div>
    @empty
        <x-empty-state />
    @endforelse
</div>
{{ $records->links() }}
@endsection
