<?php

namespace App\Services;

use App\Models\DailyDeviation;
use App\Models\DailySummaryItem;
use App\Support\DeviationCatalog;

class SummaryWording
{
    public function forDeviation(?DailyDeviation $deviation): string
    {
        if (! $deviation || $deviation->status === 'rejected') {
            return __('ui.no_deviation_reported');
        }

        if (in_array($deviation->deviation_type, DeviationCatalog::absenceTypes(), true)) {
            if ($deviation->relief_teacher_id && $deviation->action_taken !== 'relief_unavailable') {
                return __('ui.relief_assigned');
            }

            return __('ui.teacher_absent_no_relief');
        }

        $key = 'ui.deviation_text.'.$deviation->deviation_type;

        return __($key) === $key
            ? __('ui.deviation_recorded')
            : __($key);
    }

    public function fromItem(DailySummaryItem $item): string
    {
        if ($item->status === 'no_deviation' || ! $item->deviation_id) {
            return __('ui.no_deviation_reported');
        }

        $item->loadMissing('deviation');

        return $this->forDeviation($item->deviation);
    }
}
