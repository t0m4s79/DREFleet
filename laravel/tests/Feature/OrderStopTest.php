<?php

namespace Tests\Feature;

use App\Models\Kid;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Place;
use App\Models\OrderStop;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderStopTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_order_stop_belongs_to_order(): void
    {
        $order = Order::factory()->create();

        $orderStop = OrderStop::factory()->create(['order_id' => $order->id]);

        $this->assertTrue($orderStop->order->is($order));
    }

    public function test_order_stop_belongs_to_place(): void
    {
        $place = Place::factory()->create();

        $orderStop = OrderStop::factory()->create(['place_id' => $place->id]);

        $this->assertTrue($orderStop->place->is($place));
    }

    public function test_order_stop_belongs_to_many_kids(): void
    {
        $orderStop = OrderStop::factory()->create();

        $kids = Kid::factory()->count(3)->create();
    
        foreach ($kids as $kid) {
            $orderStop->kids()->attach($kid->id, ['place_id' => $orderStop->place_id]);
        }
    
        $this->assertCount(3, $orderStop->kids);
    
        foreach ($kids as $kid) {
            $this->assertTrue($orderStop->kids->contains($kid));
        }
    }

    public function test_order_stop_creation(): void
    {
        $orderStopData = [
            'expected_arrival_date' => now(),
            'stop_number' => rand(1,10),
            'order_id' => Order::factory()->create()->id,
            'place_id' => Place::factory()->create()->id,
            'kid_id' => Kid::factory()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/orderStops/create', $orderStopData);

        $response
            ->assertSessionHasNoErrors();
            // ->assertRedirect('/orders');

        $this->assertDatabaseHas('order_stops', [
            'expected_arrival_date' => $orderStopData['expected_arrival_date'],
            'stop_number' => $orderStopData['stop_number'],
            'order_id' => $orderStopData['order_id'],
            'place_id' => $orderStopData['place_id'],
        ]);

        $orderStop = OrderStop::where('order_id', $orderStopData['order_id'])
                        ->where('place_id', $orderStopData['place_id'])
                        ->where('expected_arrival_date', $orderStopData['expected_arrival_date'])
                        ->first();


        $this->assertDatabaseHas('kid_order_stop', [
            'order_stop_id' => $orderStop->id,
            'kid_id' => $orderStopData['kid_id'],
            'place_id' => $orderStopData['place_id'],
        ]);
    }

    public function test_order_stop_edit(): void
    {       
        $orderStop = OrderStop::factory()->create();

        $this->assertDatabaseHas('order_stops', [
            'expected_arrival_date' => null,
        ]);

        $updatedData = [
            'expected_arrival_date' => now(),
        ];

        $response = $this
            ->actingAs($this->user)
            ->put("/orderStops/edit/{$orderStop->id}", $updatedData);

        $response
            ->assertSessionHasNoErrors();
            // ->assertRedirect('/orders');

        $this->assertDatabaseHas('order_stops', [
            'id' => $orderStop->id,
            'expected_arrival_date' => $updatedData['expected_arrival_date'],
        ]);
    }

    public function test_order_stop_deletion(): void
    {
        $orderStop = OrderStop::factory()->create();

        $this->assertDatabaseHas('order_stops', [
            'id' => $orderStop->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/orderStops/delete/{$orderStop->id}");

        $response
            ->assertSessionHasNoErrors();
            // ->assertRedirect('/orders');

        $this->assertDatabaseMissing('order_stops', [
            'id' => $orderStop->id,
        ]);

    }

    public function test_order_stop_reached(): void
    {
        $orderStop = OrderStop::factory()->create();

        $this->assertDatabaseHas('order_stops', [
            'actual_arrival_date' => null,
        ]);

        $updatedData = [
            'actual_arrival_date' => now(),
        ];

        $response = $this
            ->actingAs($this->user)
            ->put("/orderStops/stopReached/{$orderStop->id}", $updatedData);

        $response
            ->assertSessionHasNoErrors();
            // ->assertRedirect('/orders');

        $this->assertDatabaseHas('order_stops', [
            'id' => $orderStop->id,
            'actual_arrival_date' => $updatedData['actual_arrival_date'],
        ]);
    }
}