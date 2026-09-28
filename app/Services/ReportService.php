<?php

namespace App\Services;

use App\Models\DailyDeviation;
use App\Models\DailySchedule;
use App\Models\DiscrepancyCase;
use App\Models\ParentFeedback;
use App\Models\ReliefAssignment;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\DeviationCatalog;
use App\Support\SqlDate;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    public function __construct(private SchoolAccess $access) {}

    public function monthly(User $user, array $filters): array
    {
        [$start, $end] = $this->range($filters);
        $schoolIds = $this->schoolIds($user, $filters);

        $schedules = $this->dated(DailySchedule::query(), 'date', $start, $end, $schoolIds);
        $deviations = $this->dated(DailyDeviation::query()->where('status', '!=', 'rejected'), 'date', $start, $end, $schoolIds);
        $relief = $this->dated(ReliefAssignment::query(), 'date', $start, $end, $schoolIds);
        $feedback = $this->dated(ParentFeedback::query(), 'date', $start, $end, $schoolIds);
        $cases = DiscrepancyCase::query();
        if ($schoolIds !== null) {
            $cases->whereIn('school_id', $schoolIds === [] ? [-1] : $schoolIds);
        }

        $byType = (clone $deviations)
            ->select('deviation_type', DB::raw('COUNT(*) as total'))
            ->groupBy('deviation_type')
            ->pluck('total', 'deviation_type');

        $byClass = (clone $deviations)
            ->select('class_id', DB::raw('COUNT(*) as total'))
            ->groupBy('class_id')
            ->orderByDesc('total')
            ->limit(12)
            ->get()
            ->map(function ($row) {
                $class = SchoolClass::query()->find($row->class_id);

                return [
                    'class' => $class ? $class->label() : '—',
                    'deviations' => (int) $row->total,
                ];
            })
            ->all();

        return [
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'participating_schools' => $schoolIds === null
                ? \App\Models\School::query()->where('status', 'active')->count()
                : count($schoolIds),
            'scheduled_periods' => (clone $schedules)->count(),
            'deviations' => (clone $deviations)->count(),
            'approved_teacher_absence' => (clone $deviations)->whereIn('deviation_type', DeviationCatalog::absenceTypes())->count(),
            'relief_provided' => (clone $relief)->whereNotNull('relief_teacher_id')->count(),
            'school_activities' => (clone $deviations)->whereIn('deviation_type', ['school_activity', 'sports_activity', 'assembly', 'special_program'])->count(),
            'examinations' => (clone $deviations)->where('deviation_type', 'examination')->count(),
            'periods_not_conducted' => (clone $deviations)->whereIn('deviation_type', DeviationCatalog::NOT_CONDUCTED)->count(),
            'parent_reports' => (clone $feedback)->count(),
            'discrepancies_confirmed' => (clone $cases)->whereIn('classification', ['school_record_confirmed', 'school_record_incomplete'])->count(),
            'discrepancies_unresolved' => (clone $cases)->whereIn('status', ['open', 'in_review'])->count(),
            'by_category' => $byType->all(),
            'by_class' => $byClass,
            'privacy' => 'aggregate',
        ];
    }

    public function charts(User $user, array $filters = []): array
    {
        $report = $this->monthly($user, $filters);
        [$start, $end] = $this->range($filters);
        $schoolIds = $this->schoolIds($user, $filters);

        $monthSql = SqlDate::month('date');
        $trend = DailyDeviation::query()
            ->where('status', '!=', 'rejected')
            ->when($schoolIds !== null, fn ($q) => $q->whereIn('school_id', $schoolIds ?: [-1]))
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw($monthSql.' as ym, COUNT(*) as total')
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        return [
            'report' => $report,
            'categories' => [
                'labels' => collect(array_keys($report['by_category']))->map(fn ($key) => __('ui.deviation_types.'.$key))->values()->all(),
                'data' => array_values($report['by_category']),
            ],
            'monthly' => [
                'labels' => $trend->keys()->all(),
                'data' => $trend->values()->map(fn ($v) => (int) $v)->all(),
            ],
        ];
    }

    public function pdf(User $user, array $filters)
    {
        $report = $this->monthly($user, $filters);
        $pdf = Pdf::loadView('reports.monthly-pdf', [
            'report' => $report,
            'user' => $user,
            'generatedAt' => now(),
        ])->setPaper('a4');

        return $pdf->download('sats-monthly-report-'.$report['from'].'.pdf');
    }

    public function csv(User $user, array $filters): StreamedResponse
    {
        $report = $this->monthly($user, $filters);

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Metric', 'Value']);
            foreach ($this->rows($report) as $row) {
                fputcsv($handle, $row);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['Class', 'Deviations']);
            foreach ($report['by_class'] as $class) {
                fputcsv($handle, [$class['class'], $class['deviations']]);
            }
            fclose($handle);
        }, 'sats-monthly-report.csv', ['Content-Type' => 'text/csv']);
    }

    public function xlsx(User $user, array $filters): StreamedResponse
    {
        $report = $this->monthly($user, $filters);
        $sheet = new Spreadsheet;
        $ws = $sheet->getActiveSheet();
        $ws->setTitle('Monthly report');
        $ws->setCellValue('A1', 'SATS monthly academic delivery report');
        $ws->setCellValue('A2', $report['from'].' to '.$report['to']);
        $row = 4;
        foreach ($this->rows($report) as [$label, $value]) {
            $ws->setCellValue('A'.$row, $label);
            $ws->setCellValue('B'.$row, $value);
            $row++;
        }
        $row += 1;
        $ws->setCellValue('A'.$row, 'Class');
        $ws->setCellValue('B'.$row, 'Deviations');
        foreach ($report['by_class'] as $class) {
            $row++;
            $ws->setCellValue('A'.$row, $class['class']);
            $ws->setCellValue('B'.$row, $class['deviations']);
        }

        return response()->streamDownload(function () use ($sheet) {
            (new Xlsx($sheet))->save('php://output');
        }, 'sats-monthly-report.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function rows(array $report): array
    {
        $keys = [
            'participating_schools',
            'scheduled_periods',
            'deviations',
            'approved_teacher_absence',
            'relief_provided',
            'school_activities',
            'examinations',
            'periods_not_conducted',
            'parent_reports',
            'discrepancies_confirmed',
            'discrepancies_unresolved',
        ];

        return array_map(fn ($key) => [__('ui.metrics.'.$key), $report[$key]], $keys);
    }

    private function range(array $filters): array
    {
        $start = isset($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : now()->startOfMonth();
        $end = isset($filters['to']) ? Carbon::parse($filters['to'])->endOfDay() : now()->endOfMonth();

        return [$start, $end];
    }

    /**
     * @return array<int>|null
     */
    private function schoolIds(User $user, array $filters): ?array
    {
        $allowed = $this->access->allowedSchoolIds($user);
        $query = \App\Models\School::query();
        if ($allowed !== null) {
            $query->whereIn('id', $allowed ?: [-1]);
        }
        if (! empty($filters['school_id'])) {
            $query->where('id', $filters['school_id']);
        }
        if (! empty($filters['zone_id'])) {
            $query->where('zone_id', $filters['zone_id']);
        }
        if (! empty($filters['district_id'])) {
            $query->where('district_id', $filters['district_id']);
        }
        if (! empty($filters['province_id'])) {
            $query->where('province_id', $filters['province_id']);
        }

        if ($allowed === null && empty($filters['school_id']) && empty($filters['zone_id']) && empty($filters['district_id']) && empty($filters['province_id'])) {
            return null;
        }

        return $query->pluck('id')->all();
    }

    private function dated(Builder $query, string $column, Carbon $start, Carbon $end, ?array $schoolIds): Builder
    {
        $query->whereBetween($column, [$start->toDateString(), $end->toDateString()]);
        if ($schoolIds !== null) {
            $query->whereIn('school_id', $schoolIds ?: [-1]);
        }

        return $query;
    }
}
