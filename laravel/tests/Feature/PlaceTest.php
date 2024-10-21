<?php

namespace Tests\Feature;

use App\Models\Kid;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Place;
use App\Models\OrderStop;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Testing\WithFaker;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlaceTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_place_belongs_to_many_kids(): void
    {
        $place = Place::factory()->create();

        $kids = Kid::factory()->count(3)->create();

        foreach ($kids as $kid) {
            $place->kids()->attach($kid->id);
        }

        $this->assertCount(3, $place->kids);

        foreach ($kids as $kid) {
            $this->assertTrue($place->kids->contains($kid));
        }
    }

    public function test_place_has_many_order_stops(): void
    {
        $place = Place::factory()->create();

        $orderStops = OrderStop::factory()->count(3)->create([
            'place_id' => $place->id,
            'order_id' => Order::factory(),
        ]);

        $this->assertCount(3, $place->orderStops);

        foreach ($orderStops as $orderStop) {
            $this->assertTrue($place->orderStops->contains($orderStop));
        }
    }

    public function test_places_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('places.index'));

        $response->assertOk();
    }

    public function test_place_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('places.showCreate'));

        $response->assertOk();
    }

    public function test_place_edit_page_is_displayed(): void
    {
        $place = Place::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('places.edit', $place->id));

        $response->assertOk();
    }


    public function test_user_can_create_a_place(): void
    {
        $placeData = [
            'address' => fake()->address(),
            'known_as' => Arr::random(['Casa do Avô','Casa da Tia', 'Casa do Pai', 'Casa da Mãe','Restaurante da Mãe','Casa do Primo', 'Café da Tia', 'Restaurante do Tio','Casa']),
            'place_type' => Arr::random(['Residência', 'Residência', 'Residência', 'Residência','Escola', 'Outros']),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('places.create'), $placeData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('places.index'));

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
            'place_type' => Arr::random(['Residência', 'Residência', 'Residência', 'Residência','Escola', 'Outros']),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put(route('places.edit',$place->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('places.index'));

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
            ->delete(route('places.delete', $place->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('places.index'));

        $this->assertDatabaseMissing('places', [
            'id' => $place->id,
        ]);
    }

    public function test_place_creation_handles_exception()
    {
        $data = [
            'address' => fake()->address(),
            'known_as' =>  Arr::random(['Casa do Avô','Casa da Tia', 'Casa do Pai', 'Casa da Mãe','Restaurante da Mãe','Casa do Primo', 'Café da Tia', 'Restaurante do Tio','Casa']),
            'place_type' => Arr::random(['Residência', 'Residência', 'Residência', 'Residência','Escola', 'Outros']),
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
            ->post(route('places.create'), $data);

        // Assert: Check if the catch block was executed
        $response->assertRedirect(route('places.index'));
    }
}
