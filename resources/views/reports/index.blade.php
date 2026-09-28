@extends('layouts.app')
@section('title', __('ui.monthly_report'))
@section('content')
<x-page-header :title="__('ui.monthly_report')" />
<p>{{ __('ui.privacy_note') }}</p>
<form class="panel row g-2 align-items-end mb-3" method="GET">
    <div class="col-md-3"><label class="form-label" for="from">{{ __('ui.from') }}</label><input id="from" class="form-control" type="date" name="from" value="{{ $report['from'] }}"></div>
    <div class="col-md-3"><label class="form-label" for="to">{{ __('ui.to') }}</label><input id="to" class="form-control" type="date" name="to" value="{{ $report['to'] }}"></div>
    <div class="col-md-3"><button class="btn btn-outline-secondary" type="submit">{{ __('ui.apply') }}</button></div>
    <div class="col-md-3 d-flex gap-2">
        <a class="btn btn-sats" href="{{ route('reports.export', array_merge(request()->query(), ['format' => 'pdf'])) }}">{{ __('ui.export_pdf') }}</a>
        <a class="btn btn-outline-secondary" href="{{ route('reports.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}">{{ __('ui.export_excel') }}</a>
        <a class="btn btn-outline-secondary" href="{{ route('reports.export', array_merge(request()->query(), ['format' => 'csv'])) }}">{{ __('ui.export_csv') }}</a>
    </div>
</form>
<div class="stat-grid">
    @foreach (['participating_schools','scheduled_periods','deviations','approved_teacher_absence','relief_provided','school_activities','examinations','periods_not_conducted','parent_reports','discrepancies_confirmed','discrepancies_unresolved'] as $key)
        <div class="stat-card"><div class="value">{{ number_format($report[$key]) }}</div><div class="label">{{ __('ui.metrics.'.$key) }}</div></div>
    @endforeach
</div>
<div class="panel">
    <h2 class="h6">{{ __('ui.classes') }}</h2>
    <table class="table"><thead><tr><th>{{ __('ui.class') }}</th><th>{{ __('ui.metrics.deviations') }}</th></tr></thead>
        <tbody>@foreach($report['by_class'] as $row)<tr><td>{{ $row['class'] }}</td><td>{{ $row['deviations'] }}</td></tr>@endforeach</tbody>
    </table>
    <p class="small text-secondary mb-0">{{ __('ui.no_teacher_ranking') }}</p>
</div>
@endsection
