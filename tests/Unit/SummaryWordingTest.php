<?php

namespace Tests\Unit;

use App\Models\DailyDeviation;
use App\Services\SummaryWording;
use Tests\TestCase;

class SummaryWordingTest extends TestCase
{
    public function test_absence_without_relief_does_not_claim_the_lesson_was_conducted(): void
    {
        $wording = new SummaryWording;
        $deviation = new DailyDeviation([
            'deviation_type' => 'emergency_absence',
            'status' => 'approved',
            'relief_teacher_id' => null,
        ]);

        $text = $wording->forDeviation($deviation);

        $this->assertSame('Teacher absent – no relief', $text);
        $this->assertStringNotContainsString('conducted', strtolower($text));
    }

    public function test_rejected_deviation_returns_to_no_deviation_reported(): void
    {
        $deviation = new DailyDeviation([
            'deviation_type' => 'school_activity',
            'status' => 'rejected',
        ]);

        $this->assertSame('No deviation reported', (new SummaryWording)->forDeviation($deviation));
    }
}
