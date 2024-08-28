<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Place;
use Illuminate\Support\Arr;
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

    public function test_place_creation_page_is_displayed(): void
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
            'address' => fake()->address(),
            'known_as' =>  Arr::random(['Casa do Avô','Casa da Tia', 'Casa do Pai', 'Casa da Mãe','Restaurante da Mãe','Casa do Primo', 'Café da Tia', 'Restaurante do Tio','Casa']),
            'latitude' => '29.76',
            'longitude' => '51.00',
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
        $place = Place::factory()->create();
    
        $updatedData = [
            'address' => fake()->address(),
            'known_as' =>  Arr::random(['Casa do Avô','Casa da Tia', 'Casa do Pai', 'Casa da Mãe','Restaurante da Mãe','Casa do Primo', 'Café da Tia', 'Restaurante do Tio','Casa']),
            'latitude' => '13.41',
            'longitude' => '11.2',
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put("/places/edit/{$place->id}", $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/places');

        $this->assertDatabaseHas('places', $updatedData); 
    }

    public function test_user_can_delete_a_place(): void
    {
        $place = Place::factory()->create();

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
            'address' => fake()->address(),
            'known_as' =>  Arr::random(['Casa do Avô','Casa da Tia', 'Casa do Pai', 'Casa da Mãe','Restaurante da Mãe','Casa do Primo', 'Café da Tia', 'Restaurante do Tio','Casa']),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
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
