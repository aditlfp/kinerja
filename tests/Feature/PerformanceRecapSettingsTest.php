<?php

namespace Tests\Feature;

use App\Models\PerformanceRecapSetting;
use App\Services\PerformanceRecapService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PerformanceRecapSettingsTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        PerformanceRecapSetting::query()->delete();
        parent::tearDown();
    }

    public function test_default_visible_days_are_saturday_and_sunday(): void
    {
        $service = app(PerformanceRecapService::class);

        $this->assertSame([6, 7], $service->visibleDays());
        $this->assertTrue($service->isVisible(Carbon::parse('2026-10-24')));
        $this->assertTrue($service->isVisible(Carbon::parse('2026-10-25')));
        $this->assertFalse($service->isVisible(Carbon::parse('2026-10-21')));
    }

    public function test_configured_days_control_weekly_recap_visibility(): void
    {
        PerformanceRecapSetting::create(['visible_days' => [3]]);
        $service = app(PerformanceRecapService::class);

        $this->assertTrue($service->isVisible(Carbon::parse('2026-10-21')));
        $this->assertFalse($service->isVisible(Carbon::parse('2026-10-24')));
        $this->assertFalse($service->isVisible(Carbon::parse('2026-10-29')));
    }
}
