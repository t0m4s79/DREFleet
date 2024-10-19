<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Arr;
use App\Models\OrderOccurrence;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderOccurrenceNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderOccurrenceTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_occurrence_belongs_to_order(): void
    {
        $order = Order::factory()->create();

        $occurrence = OrderOccurrence::factory()->create([
            'order_id' => $order->id,
        ]);

        $this->assertTrue($occurrence->order->is($order));
    }

    public function test_orders_occurrences_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('orderOccurrences.index'));

        $response->assertOk();
    }

    public function test_order_occurrence_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('orderOccurrences.showCreate'));

        $response->assertOk();
    }
    

    public function test_order_occurrence_edit_page_is_displayed(): void
    {
        $occurrence = OrderOccurrence::factory()->create([
            'order_id' => Order::factory()->create()
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get(route('orderOccurrences.showEdit', $occurrence->id));

        $response->assertOk();
    }

    public function test_user_can_create_an_order_occurrence(): void
    {        
        // Fake the notifications to prevent actual sending
        Notification::fake();

        $manager = User::factory()->create([
            'user_type' => 'Gestor',
        ]);

        $occurrenceData = [
            'type' => Arr::random(['Manutenções', 'Reparações', 'Lavagens', 'Outros']),
            'description' => fake()->sentence(),
            'order_id' => Order::factory()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('orderOccurrences.create'), $occurrenceData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('orders.occurrences', $occurrenceData['order_id']));

        $this->assertDatabaseHas('order_occurrences', $occurrenceData);

        // Assert that the notification was sent
        Notification::assertSentTo(
            $manager,
            OrderOccurrenceNotification::class,
            function ($notification, $channels) use ($occurrenceData) {
                $occurrence = $notification->getOccurrence(); // Use the public method
                return $occurrence->description === $occurrenceData['description'] &&
                    $occurrence->order_id === $occurrenceData['order_id'];
            }
        );
    }

    public function test_user_can_edit_an_order_occurrence(): void
    {
        $occurrance = OrderOccurrence::factory()->create([
            'order_id' => Order::factory()->create()
        ]);

        $updatedData = [
            'type' => Arr::random(['Manutenções', 'Reparações', 'Lavagens', 'Outros']),
            'description' => fake()->sentence(),
            'order_id' => Order::factory()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('orderOccurrences.edit', $occurrance->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('orders.occurrences', $updatedData['order_id']));

        $this->assertDatabaseHas('order_occurrences', $updatedData);
    }

    public function test_user_can_delete_an_order_occurrence(): void
    {
        $occurrance = OrderOccurrence::factory()->create([
            'order_id' => Order::factory()->create()
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete(route('orderOccurrences.delete', $occurrance->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('orders.occurrences', $occurrance->order->id));

        $this->assertDatabaseMissing('order_occurrences', [
            'id' => $occurrance->id,
        ]);
    }

    public function test_order_occurrence_creation_handles_exception()
    {
        $data = [
            'type' => Arr::random(['Manutenções', 'Reparações', 'Lavagens', 'Outros']),
            'description' => fake()->sentence(),
            'order_id' => Order::factory()->create()->id,
        ];

        // Mock the Order Occurrence model to throw an exception
        $this->mock(OrderOccurrence::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create order occurrence route
        $response = $this
            ->actingAs($this->user)
            ->post(route('orderOccurrences.create'), $data);

        // Assert: Check if the catch block was executed
        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('orders.occurrences',  $data['order_id']));
    }
}
