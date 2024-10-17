<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Driver;
use App\Models\OrderRoute;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_has_one_driver(): void
    {
        $user = User::factory()->create();

        $driver = Driver::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->driver->is($driver));
    }

    public function test_user_has_many_orders_as_technician(): void
    {
        $technician = User::factory()->create([
            'user_type' => 'Técnico',
        ]);

        $orders = Order::factory()->count(3)->create([
            'technician_id' => $technician->id,
        ]);

        $this->assertCount(3, $technician->ordersTechnician);

        foreach ($orders as $order) {
            $this->assertTrue($technician->ordersTechnician->contains($order));
        }
    }

    public function test_user_has_many_orders_as_manager(): void
    {
        $manager = User::factory()->create([
            'user_type' => 'Gestor',
        ]);

        $orders = Order::factory()->count(3)->create([
            'manager_id' => $manager->id,
        ]);

        $this->assertCount(3, $manager->ordersManager);

        foreach ($orders as $order) {
            $this->assertTrue($manager->ordersManager->contains($order));
        }
    }

    public function test_user_belongs_to_many_order_routes(): void
    {
        $user = User::factory()->create();

        $orderRoutes = OrderRoute::factory()->count(3)->create();

        $user->orderRoutes()->attach($orderRoutes->pluck('id'));

        $this->assertCount(3, $user->orderRoutes);

        foreach ($orderRoutes as $orderRoute) {
            $this->assertTrue($user->orderRoutes->contains($orderRoute));
        }
    }

    public function test_user_has_many_notifications(): void
    {
        $user = User::factory()->create();

        $notifications = Notification::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        foreach ($notifications as $notification) {
            $this->assertTrue($user->notifications->contains($notification));
        }
    }

    public function test_notifications_related_to_other_user()
    {
        // User who receives notification
        $user = User::factory()->create();

        // Who the notification is about
        $otherUser = User::factory()->create();

        $notification = Notification::create([
            'user_id' => $user->id,
            'related_entity_type' => User::class,
            'related_entity_id' => $otherUser->id,
            'type' => 'Utilizador',
            'title' => 'User Notification',
            'message' => 'You have a notification about the user: ' . $otherUser->id,
            'is_read' => false,
        ]);

        $this->assertCount(1, $otherUser->relatedNotifications);
        $this->assertEquals($notification->id, $user->notifications->first()->id);
    }

    public function test_users_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('users.index'));

        $response->assertOk();
    }

    public function test_user_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('users.showCreate'));

        $response->assertOk();
    }

    public function test_user_edit_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('users.showEdit', $user->id));

        $response->assertOk();
    }

    public function test_user_can_create_another_user(): void
    {

        $userData = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' =>  'Teste1234*',
            'password_confirmation' => 'Teste1234*',
            'phone' => '' . rand(910000000, 999999999),
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('users.create'), $userData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'phone' => $userData['phone'],
        ]);
    }

    public function test_user_can_edit_another_user(): void
    {
        $user = User::factory()->create();

        $updatedData = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => rand(910000000, 999999999),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido']),
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('users.edit', $user->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', $updatedData);
    }

    public function test_user_can_delete_another_user(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->delete(route('users.delete', $user->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_user_creation_handles_exception()
    {
        $data = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' =>  'Teste1234*',
            'password_confirmation' => 'Teste1234*',
            'phone' => '' . rand(910000000, 999999999),
        ];

        // Mock the User model to throw an exception
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create vehicle route
        $response = $this
            ->actingAs($this->user)
            ->post(route('users.create'), $data);

        // Assert: Check if the catch block was executed
        $response->assertRedirect(route('users.index')); // Ensure it redirects back to the form
    }
}
