<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 18mm 14mm 20mm; }
    body { font-family: DejaVu Sans, sans-serif; color: #1c2430; font-size: 12px; }
    h1 { font-size: 18px; margin: 0 0 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { border: 1px solid #d9d3c8; padding: 6px 8px; text-align: left; }
    th { background: #f4f1eb; }
    .muted { color: #5c6b7a; }
    .footer { position: fixed; bottom: -12mm; left: 0; right: 0; font-size: 10px; color: #5c6b7a; }
</style>
</head>
<body>
    <div class="footer">{{ __('ui.footer_note') }} · {{ __('ui.generated_on') }} {{ $generatedAt->format('Y-m-d H:i') }} · {{ __('ui.generated_by') }} {{ $user->name }}</div>
    <h1>{{ __('ui.monthly_report') }}</h1>
    <p class="muted">{{ $report['from'] }} — {{ $report['to'] }}</p>
    <p class="muted">{{ __('ui.privacy_note') }}</p>
    <table>
        <thead><tr><th>{{ __('ui.metrics.scheduled_periods') }}</th><th></th></tr></thead>
        <tbody>
        @foreach (['participating_schools','scheduled_periods','deviations','approved_teacher_absence','relief_provided','school_activities','examinations','periods_not_conducted','parent_reports','discrepancies_confirmed','discrepancies_unresolved'] as $key)
            <tr><td>{{ __('ui.metrics.'.$key) }}</td><td>{{ $report[$key] }}</td></tr>
        @endforeach
        </tbody>
    </table>
    <h2 style="font-size:14px;margin-top:18px">{{ __('ui.classes') }}</h2>
    <table>
        <thead><tr><th>{{ __('ui.class') }}</th><th>{{ __('ui.metrics.deviations') }}</th></tr></thead>
        <tbody>
        @foreach ($report['by_class'] as $row)
            <tr><td>{{ $row['class'] }}</td><td>{{ $row['deviations'] }}</td></tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
