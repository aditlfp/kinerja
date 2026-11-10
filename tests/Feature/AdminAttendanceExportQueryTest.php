<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class AdminAttendanceExportQueryTest extends TestCase
{
    private bool $createdPointTable = false;

    private bool $addedDeletedAtColumn = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', fn (Blueprint $table) => $table->softDeletes());
            $this->addedDeletedAtColumn = true;
        }

        if (! Schema::hasTable('points')) {
            Schema::create('points', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('client_id')->nullable();
                $table->integer('sac_point')->default(0);
                $table->timestamps();
            });
            $this->createdPointTable = true;
        }
    }

    protected function tearDown(): void
    {
        if ($this->createdPointTable) {
            Schema::dropIfExists('points');
        }

        if ($this->addedDeletedAtColumn) {
            Schema::table('users', fn (Blueprint $table) => $table->dropSoftDeletes());
        }

        parent::tearDown();
    }

    public function test_export_query_eager_loads_points_only_for_office_exports(): void
    {
        $method = new ReflectionMethod(AdminController::class, 'exportAttendanceUsers');
        $method->setAccessible(true);
        $controller = app(AdminController::class);

        $officeQuery = $method->invoke($controller, '2026-10-01', '2026-10-31', 1, null, true);
        $clientQuery = $method->invoke($controller, '2026-10-01', '2026-10-31', 2, null, false);

        $this->assertTrue($officeQuery instanceof \Illuminate\Database\Eloquent\Collection);
        $this->assertTrue($clientQuery instanceof \Illuminate\Database\Eloquent\Collection);
        $this->assertSame([], $clientQuery->map(fn (User $user) => $user->relationLoaded('absensi') ? $user->absensi->filter(fn ($row) => $row->relationLoaded('point'))->count() : 0)->all());
    }
}
