<?php

namespace Tests\Feature;

use App\Models\Kid;
use Tests\TestCase;
use App\Models\User;
use App\Models\Place;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KidTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_kids_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/kids');

        $response->assertOk();
    }

    public function test_kid_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/kids/create');

        $response->assertOk();
    }

    public function test_kid_edit_page_is_displayed(): void
    {
        $kid = Kid::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get("/kids/edit/{$kid->id}");

        $response->assertOk();
    }


    public function test_user_can_create_a_kid(): void
    {
        $kidData = [
            'wheelchair' => fake()->boolean(),
            'name' => fake()->name(),
            'phone' => rand(910000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/kids/create', $kidData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/kids');


        $this->assertDatabaseHas('kids', $kidData);
    }

    public function test_user_can_edit_a_kid(): void
    {
        $kid = Kid::factory()->create();
    
        $updatedData = [
            'wheelchair' => fake()->boolean(),
            'name' => fake()->name(),
            'phone' => rand(910000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put("/kids/edit/{$kid->id}", $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/kids');

        $this->assertDatabaseHas('kids', $updatedData); 
    }

    public function test_user_can_delete_a_kid(): void
    {
        $kid = Kid::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->delete("/kids/delete/{$kid->id}");

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/kids');

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
            'phone' => rand(910000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
            'places' => [$place_1->id, $place_2->id],
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/kids/create', $kidData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/kids');

        $this->assertDatabaseHas('kids', [
            'name' => $kidData['name'],
            'phone' => $kidData['phone'],
            'email' => $kidData['email'],
            'wheelchair' => $kidData['wheelchair'],
        ]);

        $kid = Kid::where('email', $kidData['email'])->orderBy('id', 'desc')->first();

        $this->assertDatabaseHas('kid_place', [
            'kid_id' => $kid->id,
            'place_id' => $place_1->id,
        ]);

        $this->assertDatabaseHas('kid_place', [
            'kid_id' => $kid->id,
            'place_id' => $place_2->id,
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
            'phone' => rand(910000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
            'places' => [$place_1->id, $place_2->id],
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/kids/create', $kidData);

        $kid = Kid::where('email', $kidData['email'])->orderBy('id', 'desc')->first();

        $updatedData = [
            'wheelchair' => fake()->boolean(),
            'name' => fake()->name(),
            'phone' => rand(910000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
            'addPlaces' => [$place_3->id],
            'removePlaces' => [$place_1->id, $place_2->id],
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put("/kids/edit/{$kid->id}", $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/kids');

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

    public function test_kid_creation_handles_exception()
    {
        $incomingFields = [
            'wheelchair' => fake()->boolean(),
            'name' => fake()->name(),
            'phone' => rand(910000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
        ];

        // Mock the Kid model to throw an exception
        $this->mock(Kid::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create driver route
        $response = $this
            ->actingAs($this->user)
            ->post('/kids/create', $incomingFields);

        // Assert: Check if the catch block was executed
        $response->assertRedirect('/kids'); // Ensure it redirects back to the form
    }
}
