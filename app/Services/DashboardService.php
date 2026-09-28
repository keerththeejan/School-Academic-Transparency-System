<?php

namespace App\Services;

use App\Models\DailyDeviation;
use App\Models\DailySchedule;
use App\Models\DiscrepancyCase;
use App\Models\ReliefAssignment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\DeviationCatalog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        private SchoolAccess $access,
        private ReportService $reports,
        private DailyScheduleService $schedules,
    ) {}

    public function school(User $user, ?Carbon $date = null): array
    {
        $date = $date ?: now();
        $schoolIds = $this->access->allowedSchoolIds($user);
        if ($user->school_id) {
            $school = School::query()->findOrFail($user->school_id);
            $this->schedules->ensureForSchool($school, $date);
        }

        $baseDeviation = DailyDeviation::query()->visibleTo($user)->where('status', '!=', 'rejected')->whereDate('date', $date);
        $classes = SchoolClass::query()->visibleTo($user)->where('status', 'active')->count();

        return [
            'date' => $date->toDateString(),
            'metrics' => [
                $this->metric('classes', $classes, $user->hasPermission('classes.manage') ? route('classes.index') : route('summaries.index')),
                $this->metric('scheduled_periods', DailySchedule::query()->visibleTo($user)->whereDate('date', $date)->count(), route('summaries.index', ['date' => $date->toDateString()])),
                $this->metric('deviations', (clone $baseDeviation)->count(), route('deviations.index', ['date' => $date->toDateString()])),
                $this->metric('relief_arrangements', ReliefAssignment::query()->visibleTo($user)->whereDate('date', $date)->whereNotNull('relief_teacher_id')->count(), route('relief.index', ['date' => $date->toDateString()])),
                $this->metric('periods_not_conducted', (clone $baseDeviation)->whereIn('deviation_type', DeviationCatalog::NOT_CONDUCTED)->count(), route('deviations.index', ['date' => $date->toDateString(), 'focus' => 'not_conducted'])),
                $this->metric('pending_confirmations', DailyDeviation::query()->visibleTo($user)->whereDate('date', $date)->where('status', 'pending')->count(), route('deviations.index', ['date' => $date->toDateString(), 'status' => 'pending'])),
                $this->metric('parent_discrepancies', DiscrepancyCase::query()->visibleTo($user)->whereIn('status', ['open', 'in_review'])->count(), route('discrepancies.index', ['status' => 'open'])),
            ],
            'charts' => $this->reports->charts($user, [
                'from' => $date->copy()->subMonths(5)->startOfMonth()->toDateString(),
                'to' => $date->toDateString(),
                'school_id' => $user->school_id,
            ]),
            'attention' => $this->attention($user),
        ];
    }

    public function aggregate(User $user, array $filters = []): array
    {
        $charts = $this->reports->charts($user, $filters);
        $report = $charts['report'];

        return [
            'metrics' => [
                $this->metric('participating_schools', $report['participating_schools'], route('schools.index')),
                $this->metric('scheduled_periods', $report['scheduled_periods'], route('reports.index', $filters)),
                $this->metric('deviations', $report['deviations'], route('reports.index', $filters)),
                $this->metric('relief_arrangements', $report['relief_provided'], route('reports.index', $filters)),
                $this->metric('unresolved', $report['discrepancies_unresolved'], route('reports.index', $filters)),
                $this->metric('periods_not_conducted', $report['periods_not_conducted'], route('reports.index', $filters)),
            ],
            'charts' => $charts,
            'attention' => $user->hasRole('sdc_viewer') || $user->hasRole('zonal_admin') ? $this->attention($user) : [],
            'report' => $report,
        ];
    }

    public function attention(User $user): array
    {
        $threshold = (int) config('sats.attention_threshold');
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        $rows = DailyDeviation::query()
            ->visibleTo($user)
            ->where('status', '!=', 'rejected')
            ->whereIn('deviation_type', DeviationCatalog::NOT_CONDUCTED)
            ->whereBetween('date', [$start, $end])
            ->select('class_id', DB::raw('COUNT(*) as total'))
            ->groupBy('class_id')
            ->havingRaw('COUNT(*) >= ?', [$threshold])
            ->get();

        return $rows->map(function ($row) {
            $class = SchoolClass::query()->with('school')->find($row->class_id);

            return [
                'class' => $class?->label() ?? '—',
                'school' => $class?->school?->school_name,
                'periods_not_conducted' => (int) $row->total,
            ];
        })->all();
    }

    private function metric(string $key, int $value, string $url): array
    {
        return [
            'key' => $key,
            'label' => __('ui.metrics.'.$key),
            'value' => $value,
            'url' => $url,
        ];
    }
}
