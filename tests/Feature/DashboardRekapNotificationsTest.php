<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\Rekap\DashboardRekapController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardRekapNotificationsTest extends TestCase
{
    use DatabaseTransactions;

    private bool $createdNotificationsTable = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
            $this->createdNotificationsTable = true;
        }
    }

    protected function tearDown(): void
    {
        if ($this->createdNotificationsTable) {
            Schema::dropIfExists('notifications');
        }

        parent::tearDown();
    }

    public function test_rekap_dashboard_limits_unread_notifications_to_latest_ten(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role_id' => 2])->saveQuietly();

        for ($i = 1; $i <= 12; $i++) {
            $notification = new DatabaseNotification([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => Notification::class,
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => [
                    'title' => 'Notification ' . $i,
                    'message' => 'Message ' . $i,
                    'kerjasama_id' => $i % 2 + 1,
                ],
                'created_at' => now()->addSeconds($i),
                'updated_at' => now()->addSeconds($i),
            ]);
            $notification->save();
        }

        $this->actingAs($user);
        $view = app(DashboardRekapController::class)->index(request());
        $notifications = $view->getData()['notifications'];
        $flatNotifications = $notifications->flatten();

        $this->assertCount(10, $flatNotifications);
        $this->assertSame('Notification 12', $flatNotifications->first()->data['title']);
        $this->assertSame('Notification 3', $flatNotifications->last()->data['title']);
        $this->assertSame([1, 2], $notifications->keys()->sort()->values()->all());

        $this->assertSame($user->id, $view->getData()['notifications']->flatten()->first()->notifiable_id);
    }
}
