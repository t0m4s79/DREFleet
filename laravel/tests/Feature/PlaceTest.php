<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Place;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Testing\WithFaker;
use MatanYadaev\EloquentSpatial\Objects\Point;
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
            'known_as' => Arr::random(['Casa do Avô','Casa da Tia', 'Casa do Pai', 'Casa da Mãe','Restaurante da Mãe','Casa do Primo', 'Café da Tia', 'Restaurante do Tio','Casa']),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/places/create', $placeData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/places');

        $place = Place::where('address', $placeData['address'])
                    ->where('known_as', $placeData['known_as'])
                    ->first();

        $this->assertNotNull($place);

        $expectedCoordinates = new Point($placeData['latitude'], $placeData['longitude']);

        $this->assertDatabaseHas('places', [
            'address' => $place->address,
            'known_as' =>  $place->known_as,
        ]);

        $this->assertEquals($expectedCoordinates->latitude, $place->coordinates->latitude);
        $this->assertEquals($expectedCoordinates->longitude, $place->coordinates->longitude);
    }


    public function test_user_can_edit_a_place(): void
    {
        $place = Place::factory()->create();
    
        $updatedData = [
            'address' => fake()->address(),
            'known_as' =>  Arr::random(['Casa do Avô','Casa da Tia', 'Casa do Pai', 'Casa da Mãe','Restaurante da Mãe','Casa do Primo', 'Café da Tia', 'Restaurante do Tio','Casa']),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put("/places/edit/{$place->id}", $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/places');

        $place->refresh();

        $expectedCoordinates = new Point($updatedData['latitude'], $updatedData['longitude']);
        
        $this->assertDatabaseHas('places', [
            'address' => $place->address,
            'known_as' =>  $place->known_as,
        ]);

        $this->assertEquals($expectedCoordinates->latitude, $place->coordinates->latitude);
        $this->assertEquals($expectedCoordinates->longitude, $place->coordinates->longitude);
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

        // Mock the Place model to throw an exception
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
