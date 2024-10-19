<?php

namespace Tests\Feature;

use App\Models\Kid;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Place;
use App\Models\KidEmail;
use App\Models\OrderStop;
use App\Models\Notification;
use App\Models\KidPhoneNumber;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KidTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_kid_has_many_places(): void
    {
        $kid = Kid::factory()->create();

        $places = Place::factory()->count(3)->create();

        $kid->places()->attach($places->pluck('id'));

        $this->assertCount(3, $kid->places);

        foreach ($places as $place) {
            $this->assertTrue($kid->places->contains($place));
        }
    }

    public function test_kid_has_many_order_stops(): void
    {
        $kid = Kid::factory()->create();

        $orderStops = OrderStop::factory()->count(3)->create([
            'order_id' => Order::factory(),
            'place_id' => Place::factory(),
        ]);

        foreach ($orderStops as $orderStop) {
            $kid->places()->attach($orderStop->place_id);
        }

        foreach ($orderStops as $orderStop) {
            $kid->orderStops()->attach($orderStop->id, ['place_id' => $orderStop->place_id]);
        }
                
        $this->assertCount(3, $kid->orderStops);

        foreach ($orderStops as $orderStop) {
            $this->assertTrue($kid->orderStops->contains($orderStop));
        }
    }

    public function test_kid_has_many_emails(): void
    {
        $kid = Kid::factory()->create();

        $emails = KidEmail::factory()->count(3)->create([
            'kid_id' => $kid->id,
        ]);

        foreach ($emails as $email) {
            $this->assertTrue($kid->emails->contains($email));
        }
    }

    public function test_kid_has_many_phone_numbers(): void
    {
        $kid = Kid::factory()->create();

        $phoneNumbers = KidPhoneNumber::factory()->count(3)->create([
            'kid_id' => $kid->id,
        ]);

        foreach ($phoneNumbers as $phoneNumber) {
            $this->assertTrue($kid->phoneNumbers->contains($phoneNumber));
        }
    }

    public function test_notifications_related_to_kid(): void
    {
        // User who receives notification
        $user = User::factory()->create();

        // Who the notification is about
        $kid = Kid::factory()->create();

        $notification = Notification::create([
            'user_id' => $user->id,
            'related_entity_type' => Kid::class,
            'related_entity_id' => $kid->id,
            'type' => 'Criança',
            'title' => 'Kid Notification',
            'message' => 'You have a notification about the kid: ' . $kid->id,
            'is_read' => false,
        ]);

        $this->assertCount(1, $kid->notifications);
        $this->assertEquals($notification->id, $user->notifications->first()->id);
    }

    public function test_kids_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('kids.index'));

        $response->assertOk();
    }

    public function test_kid_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('kids.showCreate'));

        $response->assertOk();
    }

    public function test_kid_edit_page_is_displayed(): void
    {
        $kid = Kid::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('kids.showEdit',$kid->id));

        $response->assertOk();
    }

    public function test_kid_contacts_page_is_displayed(): void
    {
        $kid = Kid::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('kids.contacts', $kid->id));

        $response->assertOk();
    }

    public function test_user_can_create_a_kid(): void
    {
        $kidData = [
            'wheelchair' => fake()->boolean(),
            'name' => fake()->name(),
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('kids.create'), $kidData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kids.index'));


        $this->assertDatabaseHas('kids', $kidData);
    }

    public function test_user_can_edit_a_kid(): void
    {
        $kid = Kid::factory()->create();
    
        $updatedData = [
            'wheelchair' => fake()->boolean(),
            'name' => fake()->name(),
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put(route('kids.edit', $kid->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kids.index'));

        $this->assertDatabaseHas('kids', $updatedData); 
    }

    public function test_user_can_delete_a_kid(): void
    {
        $kid = Kid::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->delete(route('kids.delete', $kid->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kids.index'));

        $this->assertDatabaseMissing('kids', [
            'id' => $kid->id,
        ]);
    }

    public function test_user_can_create_a_kid_with_places(): void
    {
        $place_1 = Place::factory()->create(['place_type' => 'Residência']);
        $place_2 = Place::factory()->create(['place_type' => 'Residência']);

        $kidData = [
            'wheelchair' => fake()->boolean(),
            'name' => fake()->name(),
            'places' => [$place_1->id, $place_2->id],
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/kids/create', $kidData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kids.index'));

        $this->assertDatabaseHas('kids', [
            'name' => $kidData['name'],
            'wheelchair' => $kidData['wheelchair'],
        ]);

        $kid = Kid::where('name', $kidData['name'])->orderBy('id', 'desc')->first();

        $this->assertDatabaseHas('kid_place', [
            'kid_id' => $kid->id,
            'place_id' => $place_1->id,
        ]);

        $this->assertDatabaseHas('kid_place', [
            'kid_id' => $kid->id,
            'place_id' => $place_2->id,
        ]);
    }

    public function test_create_kid_fails_on_wrong_place_type(): void
    {
        $place_1 = Place::factory()->create(['place_type' => 'Residência',]);
        $place_2 = Place::factory()->create(['place_type' => 'Escola',]);

        $kidData = [
            'wheelchair' => fake()->boolean(),
            'name' => fake()->name(),
            'places' => [$place_1->id, $place_2->id],
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/kids/create', $kidData);

        $response->assertSessionHasErrors(['places']);

        $this->assertDatabaseMissing('kids',[
            'name' => $kidData['name'],
            'wheelchair' => $kidData['wheelchair'],
        ]);
    }
    
    public function test_user_can_edit_a_kid_and_their_places(): void
    {
        $place_1 = Place::factory()->create(['place_type' => 'Residência']);
        $place_2 = Place::factory()->create(['place_type' => 'Residência']);
        $place_3 = Place::factory()->create(['place_type' => 'Residência']);

        $kidData = [
            'wheelchair' => fake()->boolean(),
            'name' => fake()->name(),
            'places' => [$place_1->id, $place_2->id],
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/kids/create', $kidData);

        $kid = Kid::where('name', $kidData['name'])->orderBy('id', 'desc')->first();

        $updatedData = [
            'wheelchair' => fake()->boolean(),
            'name' => fake()->name(),
            'addPlaces' => [$place_3->id],
            'removePlaces' => [$place_1->id, $place_2->id],
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put(route('kids.edit', $kid->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kids.index'));

        $this->assertDatabaseMissing('kid_place', [
            'kid_id' => $kid->id,
            'place_id' => $place_1->id,
        ]);

        $this->assertDatabaseMissing('kid_place', [
            'kid_id' => $kid->id,
            'place_id' => $place_2->id,
        ]);

        $this->assertDatabaseHas('kid_place', [
            'kid_id' => $kid->id,
            'place_id' => $place_3->id,
        ]);
        
    }

    public function test_edit_kid_fails_on_wrong_place_type(): void
    {
        $place_1 = Place::factory()->create(['place_type' => 'Residência',]);
        $place_2 = Place::factory()->create(['place_type' => 'Escola',]);

        $kidData = [
            'wheelchair' => fake()->boolean(),
            'name' => fake()->name(),
            'places' => [$place_1->id],
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/kids/create', $kidData);

        $kid = Kid::where('name', $kidData['name'])->orderBy('id', 'desc')->first();

        $updatedData = [
            'wheelchair' => fake()->boolean(),
            'name' => fake()->name(),
            'addPlaces' => [$place_2->id],
            'removePlaces' => [$place_1->id],
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put(route('kids.edit', $kid->id), $updatedData);

        $response->assertSessionHasErrors(['addPlaces']);

        $this->assertDatabaseMissing('kids',[
            'name' => $updatedData['name'],
            'wheelchair' => $updatedData['wheelchair'],
        ]);
    }

    public function test_kid_creation_handles_exception()
    {
        $data = [
            'wheelchair' => fake()->boolean(),
            'name' => fake()->name(),
        ];

        // Mock the Kid model to throw an exception
        $this->mock(Kid::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create kid route
        $response = $this
            ->actingAs($this->user)
            ->post('/kids/create', $data);

        // Assert: Check if the catch block was executed
        $response->assertRedirect(route('kids.index')); // Ensure it redirects back to the form
    }
}
