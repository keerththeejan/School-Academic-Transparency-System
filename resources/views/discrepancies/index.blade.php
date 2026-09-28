@extends('layouts.app')
@section('title', __('ui.discrepancies'))
@section('content')
<x-page-header :title="__('ui.discrepancies')" />
<div class="panel">
    @forelse ($records as $case)
        <div class="d-flex justify-content-between border-bottom py-2">
            <div>{{ $case->feedback->date->toDateString() }} · {{ $case->feedback->student->schoolClass?->label() }} · {{ __('ui.'.$case->status) }}</div>
            <a href="{{ route('discrepancies.show', $case) }}">{{ __('ui.view') }}</a>
        </div>
    @empty
        <x-empty-state />
    @endforelse
</div>
{{ $records->links() }}
@endsection
