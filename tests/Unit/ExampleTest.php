<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_deviation_catalog_has_no_ranking_metric(): void
    {
        $keys = \App\Support\DeviationCatalog::keys();

        $this->assertNotContains('teacher_score', $keys);
        $this->assertContains('lesson_not_conducted', $keys);
    }
}
