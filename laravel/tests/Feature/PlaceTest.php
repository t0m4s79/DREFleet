<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Place;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlaceTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_places_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/places');

        $response->assertOk();
    }

    public function test_places_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/places/create');

        $response->assertOk();
    }

    public function test_place_edit_page_is_displayed(): void
    {
        $place = Place::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get("/places/edit/{$place->id}");

        $response->assertOk();
    }


    public function test_user_can_create_a_place(): void
    {
        $placeData = [
            'address' => 'Casa do Ronaldo',
            'known_as' => 'House of the Best',
            'latitude' => '77.7',
            'longitude' => '9.11',
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/places/create', $placeData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/places');


        $this->assertDatabaseHas('places', $placeData);
    }

    public function test_user_can_edit_a_place(): void
    {
        $place = Place::factory()->create([
            'address' => 'Casa do Ronaldo',
            'known_as' => 'House of the Best',
            'latitude' => '77.7',
            'longitude' => '9.11',
        ]);
    
        $updatedData = [
            'address' => 'Casa do Messi',
            'known_as' => 'Casa do segundo melhor',
            'latitude' => '10.01',
            'longitude' => '1.11',
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put("/places/edit/{$place->id}", $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/places');

        $this->assertDatabaseHas('places', [
            'address' => 'Casa do Messi',
            'known_as' => 'Casa do segundo melhor',
            'latitude' => '10.01',
            'longitude' => '1.11',
        ]); 
    }

    public function test_user_can_delete_a_place(): void
    {
        $place = Place::factory()->create([
            'address' => 'Casa do Messi',
            'known_as' => 'Casa do segundo melhor',
            'latitude' => '10.01',
            'longitude' => '1.11',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/places/delete/{$place->id}");

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/places');

        $this->assertDatabaseMissing('places', [
            'id' => $place->id,
        ]);
    }

    public function test_place_creation_handles_exception()
    {
        $incomingFields = [
            'address' => 'Casa do Messi',
            'known_as' => 'Casa do segundo melhor',
            'latitude' => '10.01',
            'longitude' => '1.11',
        ];

        // Mock the Vehicle model to throw an exception
        $this->mock(Place::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create place route
        $response = $this
            ->actingAs($this->user)
            ->post('/places/create', $incomingFields);

        // Assert: Check if the catch block was executed
        $response->assertRedirect('/places');
    }
}
