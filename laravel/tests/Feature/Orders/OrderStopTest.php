<?php

namespace Tests\Feature;

use App\Models\Kid;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Place;
use App\Models\OrderStop;
use Illuminate\Support\Carbon;
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

        $orderStop = OrderStop::factory()->create([
            'order_id' => $order->id,
            'place_id' => Place::factory(),
        ]);

        $this->assertTrue($orderStop->order->is($order));
    }

    public function test_order_stop_belongs_to_place(): void
    {
        $place = Place::factory()->create();

        $orderStop = OrderStop::factory()->create([
            'place_id' => $place->id,
            'order_id' => Order::factory(),
        ]);

        $this->assertTrue($orderStop->place->is($place));
    }

    public function test_order_stop_belongs_to_many_kids(): void
    {
        $orderStop = OrderStop::factory()->create([
            'order_id' => Order::factory(),
            'place_id' => Place::factory(),
        ]);

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
            'expected_arrival_date' => now()->toDateTimeString(),
            'stop_number' => rand(1,10),
            'order_id' => Order::factory()->create()->id,
            'place_id' => Place::factory()->create()->id,
            'time_from_previous_stop' => rand(1,200),
            'distance_from_previous_stop' => rand(1,200),
            'kid_id' => Kid::factory()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('orderStops.create'), $orderStopData);

        $response
            ->assertSessionHasNoErrors();
            // ->assertRedirect('/orders');

        $this->assertDatabaseHas('order_stops', [
            'expected_arrival_date' => $orderStopData['expected_arrival_date'],
            'stop_number' => $orderStopData['stop_number'],
            'time_from_previous_stop' => $orderStopData['time_from_previous_stop'],
            'distance_from_previous_stop' => $orderStopData['distance_from_previous_stop'],
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
        $orderStop = OrderStop::factory()->create([
            'order_id' => Order::factory(),
            'place_id' => Place::factory(),
        ]);

        $updatedData = [
            'expected_arrival_date' => Carbon::parse($orderStop->expected_arrival_date)->addHour(),
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('orderStops.edit', $orderStop->id), $updatedData);

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
        $orderStop = OrderStop::factory()->create([
            'order_id' => Order::factory(),
            'place_id' => Place::factory(),
        ]);

        $this->assertDatabaseHas('order_stops', [
            'id' => $orderStop->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete(route('orderStops.delete', $orderStop->id));

        $response
            ->assertSessionHasNoErrors();
            // ->assertRedirect('/orders');

        $this->assertDatabaseMissing('order_stops', [
            'id' => $orderStop->id,
        ]);
    }

    public function test_order_stop_reached(): void
    {
        $orderStop = OrderStop::factory()->create([
            'order_id' => Order::factory(),
            'place_id' => Place::factory(),
        ]);

        $this->assertDatabaseHas('order_stops', [
            'actual_arrival_date' => null,
        ]);

        $updatedData = [
            'actual_arrival_date' => now(),
        ];

        $response = $this
            ->actingAs($this->user)
            ->patch(route('orderStops.stopReached', $orderStop->id), $updatedData);

        $response
            ->assertSessionHasNoErrors();
            // ->assertRedirect('/orders');

        $this->assertDatabaseHas('order_stops', [
            'id' => $orderStop->id,
            'actual_arrival_date' => $updatedData['actual_arrival_date'],
        ]);
    }
}