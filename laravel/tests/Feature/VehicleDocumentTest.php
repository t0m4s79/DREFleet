<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use App\Models\VehicleDocument;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehicleDocumentTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_document_belongs_to_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $vehicleDocument = VehicleDocument::factory()->create(['vehicle_id' => $vehicle->id]);

        $this->assertTrue($vehicleDocument->vehicle->is($vehicle));
    }

    public function test_vehicle_documents_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/vehicleDocuments');

        $response->assertOk();
    }

    public function test_vehicle_document_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/vehicleDocuments/create');

        $response->assertOk();
    }

    public function test_vehicle_document_edit_page_is_displayed(): void
    {
        $vehicleDocument = VehicleDocument::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get("/vehicleDocuments/edit/{$vehicleDocument->id}");

        $response->assertOk();
    }

    public function test_user_can_create_a_vehicle_document(): void
    {
        $vehicleDocumentData = [
            'name' => fake()->name(),
            'issue_date' => fake()->dateTime()->format('Y-m-d'),
            'expiration_date' => fake()->dateTime()->format('Y-m-d'),
            'vehicle_id' => Vehicle::factory()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/vehicleDocuments/create', $vehicleDocumentData);

        $previousUrl = url()->previous();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect($previousUrl);

        $this->assertDatabaseHas('vehicle_documents', $vehicleDocumentData);
    }

    public function test_user_can_edit_a_vehicle_document(): void
    {
        $vehicleDocument = VehicleDocument::factory()->create();

        $updatedData = [
            'name' => fake()->name(),
            'issue_date' => fake()->dateTime()->format('Y-m-d'),
            'expiration_date' => fake()->dateTime()->format('Y-m-d'),
            'vehicle_id' => $vehicleDocument->vehicle_id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->put("/vehicleDocuments/edit/{$vehicleDocument->id}", $updatedData);

        $previousUrl = url()->previous();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect($previousUrl);

        $this->assertDatabaseHas('vehicle_documents', $updatedData);
    }

    public function test_user_can_delete_a_vehicle(): void
    {
        $vehicleDocument = VehicleDocument::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->delete("/vehicleDocuments/delete/{$vehicleDocument->id}");

        $previousUrl = url()->previous();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect($previousUrl);

        $this->assertDatabaseMissing('vehicle_documents', [
            'id' => $vehicleDocument->id,
        ]);
    }

    public function test_vehicle_creation_handles_exception()
    {
        // Prepare the incoming fields
        $incomingFields = [
            'name' => fake()->name(),
            'issue_date' => fake()->dateTime()->format('Y-m-d'),
            'expiration_date' => fake()->dateTime()->format('Y-m-d'),
            'vehicle_id' => Vehicle::factory()->create()->id,
        ];

        // Mock the Vehicle Document model to throw an exception
        $this->mock(VehicleDocument::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create vehicle document route
        $response = $this
            ->actingAs($this->user)
            ->post('/vehicleDocuments/create', $incomingFields);

        // Assert: Check if the catch block was executed
        $previousUrl = url()->previous();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect($previousUrl);
    }
}
