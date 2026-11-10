<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ApprovalNotificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\TestCase;

class ApprovalNotificationLookupTest extends TestCase
{
    use DatabaseTransactions;

    private bool $createdNotificationsTable = false;

    private bool $addedLookupColumns = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->string('notification_type')->nullable();
                $table->unsignedBigInteger('kerjasama_id')->nullable();
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
            $this->createdNotificationsTable = true;
        } elseif (! Schema::hasColumn('notifications', 'notification_type')) {
            Schema::table('notifications', function (Blueprint $table): void {
                $table->string('notification_type')->nullable()->after('type');
                $table->unsignedBigInteger('kerjasama_id')->nullable()->after('notification_type');
            });
            $this->addedLookupColumns = true;
        }
    }

    protected function tearDown(): void
    {
        if ($this->createdNotificationsTable) {
            Schema::dropIfExists('notifications');
        } elseif ($this->addedLookupColumns) {
            Schema::table('notifications', function (Blueprint $table): void {
                $table->dropColumn(['notification_type', 'kerjasama_id']);
            });
        }

        parent::tearDown();
    }

    public function test_unread_duplicate_lookup_uses_lookup_columns(): void
    {
        $recipient = User::factory()->create();
        $other = User::factory()->create();

        foreach ([[$recipient, 'person_in', 7], [$recipient, 'person_in', 8], [$other, 'person_in', 8]] as [$user, $type, $kerjasamaId]) {
            DatabaseNotification::query()->create([
                'id' => (string) Str::uuid(),
                'type' => 'database',
                'notification_type' => $type,
                'kerjasama_id' => $kerjasamaId,
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => ['type' => $type, 'kerjasama_id' => $kerjasamaId],
            ]);
        }

        $method = new ReflectionMethod(ApprovalNotificationService::class, 'withoutUnreadNotification');
        $method->setAccessible(true);
        $result = $method->invoke(app(ApprovalNotificationService::class), collect([$recipient, $other]), 'person_in', 7);

        $this->assertSame([$other->id], $result->pluck('id')->all());
    }
}
