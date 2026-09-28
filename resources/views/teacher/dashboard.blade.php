@extends('layouts.app')
@section('title', __('ui.today_classes'))
@section('content')
<x-page-header :title="__('ui.today_classes')" />
<div class="panel">
    @forelse ($slots as $slot)
        <div class="d-flex justify-content-between align-items-center border-bottom py-3">
            <div>
                <div class="fw-semibold">{{ app(\App\Services\DailyScheduleService::class)->periodLabel($slot->period, $slot->school_id) }} · {{ $slot->schoolClass->label() }}</div>
                <div>{{ $slot->subject?->subject_name }}</div>
            </div>
            <a class="btn btn-sats" href="{{ route('teacher.deviate', $slot) }}">{{ __('ui.report_deviation') }}</a>
        </div>
    @empty
        <x-empty-state />
    @endforelse
</div>
@endsection
