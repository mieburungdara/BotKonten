<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notification;
use App\Models\Bot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test bot for notifications
        Bot::create([
            'name' => 'Test Bot',
            'token' => 'test_token_' . rand(1000, 9999),
            'username' => 'testbot',
            'webhook_url' => 'https://example.com/webhook/test',
        ]);
    }

    public function test_user_can_get_notifications()
    {
        $user = User::factory()->create(['telegram_id' => 123456789]);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'test',
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
        ]);

        $response = $this->actingAs($user)->getJson('/api/notifications');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'notifications',
                    'unread_count'
                ]);
    }

    public function test_notification_creation_triggers_event()
    {
        Event::fake();

        $user = User::factory()->create(['telegram_id' => 123456789]);

        $response = $this->actingAs($user)->postJson('/api/notifications', [
            'user_id' => $user->id,
            'type' => 'test',
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
        ]);

        $response->assertStatus(201);

        Event::assertDispatched(\App\Events\NotificationSent::class);
    }

    public function test_draft_publish_sends_notification()
    {
        Event::fake();

        $user = User::factory()->create(['telegram_id' => 123456789]);

        // This would normally be called from MediaController::publish
        Notification::create([
            'user_id' => $user->id,
            'type' => 'draft_published',
            'title' => 'Draft Berhasil Dipublish!',
            'message' => 'Media test telah berhasil dipublish',
            'data' => ['media_id' => 1],
        ]);

        // Verify notification was created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'draft_published',
            'title' => 'Draft Berhasil Dipublish!',
        ]);
    }
}