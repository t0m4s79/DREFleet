<?php

namespace Tests\Feature;

use App\Models\Kid;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_notification_getters(): void
    {
        // Create a sample notification with specific created_at and updated_at values
        $notification = Notification::factory()->create([
            'created_at' => Carbon::create(2023, 10, 1, 12, 0, 0),  // 01-10-2023 12:00:00
            'updated_at' => Carbon::create(2023, 10, 2, 15, 30, 45), // 02-10-2023 15:30:45
        ]);

        // Get the formatted created_at and updated_at attributes
        $formattedCreatedAt = $notification->created_at;
        $formattedUpdatedAt = $notification->updated_at;

        // Assert that the created_at is in the correct format
        $this->assertEquals('01-10-2023 12:00:00', $formattedCreatedAt);

        // Assert that the updated_at is in the correct format
        $this->assertEquals('02-10-2023 15:30:45', $formattedUpdatedAt);
    }

    public function test_notification_belongs_to_a_user(): void
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue($notification->user->is($user));
    }

    public function test_notification_belongs_to_entity(): void
    {
        $user = User::factory()->create();

        // Randomly choose an entity type and create an instance of that entity
        $type = fake()->randomElement(['Veículo', 'Pedido', 'Utilizador', 'Condutor', 'Criança']);

        switch ($type) {
            case 'Veículo':
                $relatedEntityId = Vehicle::factory()->create()->id;
                $relatedEntityType = Vehicle::class;
                break;
            case 'Pedido':
                $relatedEntityId = Order::factory()->create()->id;
                $relatedEntityType = Order::class;
                break;
            case 'Utilizador':
                $relatedEntityId = User::factory()->create()->id;
                $relatedEntityType = User::class;
                break;
            case 'Condutor':
                $relatedEntityId = Driver::factory()->create()->user_id;
                $relatedEntityType = Driver::class;
                break;
            case 'Criança':
                $relatedEntityId = Kid::factory()->create()->id;
                $relatedEntityType = Kid::class;
                break;
        }

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'related_entity_type' => $relatedEntityType,
            'related_entity_id' => $relatedEntityId,
        ]);

        $this->assertInstanceOf($relatedEntityType, $notification->relatedEntity);
        
        if ($type == 'Condutor') {
            $this->assertEquals($relatedEntityId, $notification->relatedEntity->user_id);
        
        } else {
            $this->assertEquals($relatedEntityId, $notification->relatedEntity->id);
        } 
    }

    public function test_notifications_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/notifications');

        $response->assertOk();
    }

    public function test_user_marks_notification_as_read()
    {
        $notification = Notification::factory()->create(['user_id' => $this->user->id, 'is_read' => false]);

        $response = $this
            ->actingAs($this->user)
            ->patch(route('notifications.markAsRead', ['notification' => $notification->id]));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/notifications');

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => 1,
        ]);

        $response->assertRedirect(route('notifications.index'));
    }

    public function test_user_marks_notification_as_read_fails_on_not_his_notification()
    {
        $otherUser = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $otherUser->id, 'is_read' => false]);

        $response = $this
            ->actingAs($this->user)
            ->patch(route('notifications.markAsRead', ['notification' => $notification->id]));

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => 0,
        ]);

        $response->assertRedirect();
    }

    public function test_user_can_delete_notification()
    {
        $notification = Notification::factory()->create();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/notifications/delete/{$notification->id}");

        $response
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
        ]);
    }
}