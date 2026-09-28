@extends('layouts.app')
@section('title', __('ui.dashboard'))
@section('content')
<x-page-header :title="__('ui.dashboard')" />
<p class="text-secondary">{{ $overview['date'] }} · {{ __('ui.tagline') }}</p>
<div class="stat-grid">
    @foreach ($overview['metrics'] as $metric)
        <a class="stat-card" href="{{ $metric['url'] }}">
            <div class="value">{{ number_format($metric['value']) }}</div>
            <div class="label">{{ $metric['label'] }}</div>
        </a>
    @endforeach
</div>
<div class="row g-3 mt-1">
    <div class="col-lg-7"><div class="panel chart-box"><h2 class="h6">{{ __('ui.metrics.deviations') }}</h2><canvas id="monthlyChart" height="120"></canvas></div></div>
    <div class="col-lg-5"><div class="panel chart-box"><h2 class="h6">{{ __('ui.deviation_groups.teacher') }}</h2><canvas id="categoryChart" height="140"></canvas></div></div>
</div>
@if (!empty($overview['attention']))
    <div class="panel">
        <h2 class="h5">{{ __('ui.classes_requiring_attention') }}</h2>
        <p class="text-secondary">{{ __('ui.attention_help') }}</p>
        <ul class="mb-0">
            @foreach ($overview['attention'] as $row)
                <li>{{ $row['class'] }} @if($row['school']) · {{ $row['school'] }} @endif — {{ $row['periods_not_conducted'] }}</li>
            @endforeach
        </ul>
    </div>
@endif
<p class="text-secondary small mt-3">{{ __('ui.no_teacher_ranking') }}</p>
@include('partials.charts', ['charts' => $overview['charts']])
@endsection
