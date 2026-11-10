<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardController;
use App\Models\CheckPoint;
use App\Models\CheckPointItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class DashboardPendingCheckpointsTest extends TestCase
{
    use DatabaseTransactions;

    private bool $createdItemsTable = false;

    private bool $addedDeletedAtColumn = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->softDeletes();
            });
            $this->addedDeletedAtColumn = true;
        }

        if (! Schema::hasTable('check_point_items')) {
            Schema::create('check_point_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('check_point_id');
                $table->string('pekerjaan_cp_id')->nullable();
                $table->text('input_manual')->nullable();
                $table->date('tanggal')->nullable();
                $table->string('approve_status')->default('proccess');
                $table->text('note')->nullable();
                $table->unsignedInteger('urutan')->default(0);
                $table->timestamps();
                $table->index('check_point_id');
            });
            $this->createdItemsTable = true;
        }
    }

    protected function tearDown(): void
    {
        if ($this->createdItemsTable) {
            Schema::dropIfExists('check_point_items');
        }

        if ($this->addedDeletedAtColumn) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }

        parent::tearDown();
    }

    public function test_pending_direksi_checkpoint_query_excludes_settled_items(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['nama_lengkap' => 'Pending Dashboard Employee'])->saveQuietly();

        $pending = CheckPoint::create([
            'user_id' => $user->id,
            'type_check' => 'dikerjakan',
            'created_at' => Carbon::parse('2026-11-10'),
            'updated_at' => Carbon::parse('2026-11-10'),
        ]);
        CheckPointItem::create([
            'check_point_id' => $pending->id,
            'tanggal' => '2026-11-10',
            'approve_status' => 'proccess',
            'input_manual' => 'Pending job',
        ]);

        $settled = CheckPoint::create([
            'user_id' => $user->id,
            'type_check' => 'dikerjakan',
            'created_at' => Carbon::parse('2026-11-11'),
            'updated_at' => Carbon::parse('2026-11-11'),
        ]);
        CheckPointItem::create([
            'check_point_id' => $settled->id,
            'tanggal' => '2026-11-11',
            'approve_status' => 'accept',
            'input_manual' => 'Settled job',
        ]);

        $method = new ReflectionMethod(DashboardController::class, 'pendingDireksiCheckpoints');
        $method->setAccessible(true);
        $result = $method->invoke(
            app(DashboardController::class),
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-12-31 23:59:59'),
        );

        $this->assertCount(1, $result);
        $this->assertSame('Pending Dashboard Employee', $result->first()->employee);
        $this->assertSame('10', $result->first()->dates);
    }
}
