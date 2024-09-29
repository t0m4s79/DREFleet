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
        
    }

    public function test_order_stop_edit(): void
    {       
        
    }

    public function test_order_stop_deletion(): void
    {

    }
}
