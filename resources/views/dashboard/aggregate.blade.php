@extends('layouts.app')
@section('title', __('ui.aggregate_only'))
@section('content')
<x-page-header :title="$title" />
<p class="text-secondary">{{ __('ui.privacy_note') }}</p>
<form class="panel row g-2 align-items-end mb-3" method="GET">
    <div class="col-md-3"><label class="form-label" for="from">{{ __('ui.from') }}</label><input id="from" type="date" name="from" value="{{ request('from', $overview['report']['from']) }}" class="form-control"></div>
    <div class="col-md-3"><label class="form-label" for="to">{{ __('ui.to') }}</label><input id="to" type="date" name="to" value="{{ request('to', $overview['report']['to']) }}" class="form-control"></div>
    <div class="col-md-2"><button class="btn btn-sats" type="submit">{{ __('ui.apply') }}</button></div>
</form>
<div class="stat-grid">
    @foreach ($overview['metrics'] as $metric)
        <a class="stat-card" href="{{ $metric['url'] }}"><div class="value">{{ number_format($metric['value']) }}</div><div class="label">{{ $metric['label'] }}</div></a>
    @endforeach
</div>
<div class="row g-3 mt-1">
    <div class="col-lg-7"><div class="panel"><h2 class="h6">{{ __('ui.recurring') }}</h2><canvas id="monthlyChart" height="120"></canvas></div></div>
    <div class="col-lg-5"><div class="panel"><h2 class="h6">{{ __('ui.metrics.deviations') }}</h2><canvas id="categoryChart" height="140"></canvas></div></div>
</div>
@if (!empty($overview['attention']))
    <div class="panel">
        <h2 class="h5">{{ __('ui.classes_requiring_attention') }}</h2>
        <p class="text-secondary">{{ __('ui.attention_help') }}</p>
        <ul>@foreach ($overview['attention'] as $row)<li>{{ $row['class'] }} @if($row['school'])({{ $row['school'] }})@endif — {{ $row['periods_not_conducted'] }}</li>@endforeach</ul>
    </div>
@endif
@include('partials.charts', ['charts' => $overview['charts']])
@endsection
