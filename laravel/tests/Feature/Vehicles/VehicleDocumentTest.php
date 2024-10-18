<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
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
            ->get(route('vehicleDocuments.index'));

        $response->assertOk();
    }

    public function test_vehicle_document_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicleDocuments.showCreate'));

        $response->assertOk();
    }

    public function test_vehicle_document_edit_page_is_displayed(): void
    {
        $vehicleDocument = VehicleDocument::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicleDocuments.showEdit', $vehicleDocument->id));

        $response->assertOk();
    }

    public function test_user_can_create_a_vehicle_document(): void
    {
        $issueDate = Carbon::instance(fake()->dateTime());
        
        $vehicleDocumentData = [
            'name' => fake()->name(),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiration_date' => $issueDate->copy()->addYear(1)->format('Y-m-d'),
            'vehicle_id' => Vehicle::factory()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicleDocuments.create'), $vehicleDocumentData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.documentsAndAccessories', $vehicleDocumentData['vehicle_id']));

        $this->assertDatabaseHas('vehicle_documents', $vehicleDocumentData);
    }

    public function test_user_can_create_a_vehicle_document_with_aditional_data(): void
    {
        $issueDate = Carbon::instance(fake()->dateTime());

        $data = [];
        for ($i = 0; $i < rand(0, 3); $i++) {
            $key = fake()->name(); // Random key
            $value = fake()->name(); // Random value
            $data[$key] = $value;
        }
        
        $vehicleDocumentData = [
            'name' => fake()->name(),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiration_date' => $issueDate->copy()->addYear(1)->format('Y-m-d'),
            'vehicle_id' => Vehicle::factory()->create()->id,
            'data' => $data,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicleDocuments.create'), $vehicleDocumentData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.documentsAndAccessories', $vehicleDocumentData['vehicle_id']));

        $this->assertDatabaseHas('vehicle_documents', [
            'name' => $vehicleDocumentData['name'],
            'issue_date' => $vehicleDocumentData['issue_date'],
            'expiration_date' => $vehicleDocumentData['expiration_date'],
            'vehicle_id' => $vehicleDocumentData['vehicle_id'],
            'data' => $vehicleDocumentData['data'] != [] ? json_encode($vehicleDocumentData['data']) : null,
        ]);
    }

    public function test_vehicle_document_data_fails_validation_on_creation(): void
    {
        $issueDate = Carbon::instance(fake()->dateTime());

        // Fail on empty key
        $data_1 = [];
        $key_1 = '';
        $value_1 = 'asd';
        $data_1[$key_1] = $value_1;
                
        $vehicleDocumentData_1 = [
            'name' => fake()->name(),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiration_date' => $issueDate->copy()->addYear(1)->format('Y-m-d'),
            'vehicle_id' => Vehicle::factory()->create()->id,
            'data' => $data_1,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicleDocuments.create'), $vehicleDocumentData_1);

        $response->assertSessionHasErrors(['data']);


        $this->assertDatabaseMissing('vehicle_documents', [
            'name' => $vehicleDocumentData_1['name'],
            'issue_date' => $vehicleDocumentData_1['issue_date'],
            'expiration_date' => $vehicleDocumentData_1['expiration_date'],
            'vehicle_id' => $vehicleDocumentData_1['vehicle_id'],
            'data' => json_encode($vehicleDocumentData_1['data']),
        ]);

        // Fail on empty value
        $data_2 = [];
        $key_2 = '';
        $value_2 = 'asd';
        $data_2[$key_2] = $value_2;
                
        $vehicleDocumentData_2 = [
            'name' => fake()->name(),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiration_date' => $issueDate->copy()->addYear(1)->format('Y-m-d'),
            'vehicle_id' => Vehicle::factory()->create()->id,
            'data' => $data_2,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicleDocuments.create'), $vehicleDocumentData_2);

        $response->assertSessionHasErrors(['data']);

        $this->assertDatabaseMissing('vehicle_documents', [
            'name' => $vehicleDocumentData_2['name'],
            'issue_date' => $vehicleDocumentData_2['issue_date'],
            'expiration_date' => $vehicleDocumentData_2['expiration_date'],
            'vehicle_id' => $vehicleDocumentData_2['vehicle_id'],
            'data' => json_encode($vehicleDocumentData_2['data']),
        ]);
    }

    public function test_user_can_edit_a_vehicle_document(): void
    {
        $issueDate = Carbon::instance(fake()->dateTime());
        $vehicleDocument = VehicleDocument::factory()->create();

        $updatedData = [
            'name' => fake()->name(),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiration_date' => $issueDate->copy()->addYear(1)->format('Y-m-d'),
            'vehicle_id' => $vehicleDocument->vehicle_id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('vehicleDocuments.edit', $vehicleDocument->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.documentsAndAccessories', $updatedData['vehicle_id']));

        $this->assertDatabaseHas('vehicle_documents', $updatedData);
    }

    public function test_user_can_edit_a_vehicle_document_with_aditional_data(): void
    {
        $issueDate = Carbon::instance(fake()->dateTime());
        $vehicleDocument = VehicleDocument::factory()->create();

        $data = [];
        for ($i = 0; $i < rand(0, 3); $i++) {
            $key = fake()->name(); // Random key
            $value = fake()->name(); // Random value
            $data[$key] = $value;
        }

        $updatedData = [
            'name' => fake()->name(),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiration_date' => $issueDate->copy()->addYear(1)->format('Y-m-d'),
            'vehicle_id' => $vehicleDocument->vehicle_id,
            'data' => $data,
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('vehicleDocuments.edit', $vehicleDocument->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.documentsAndAccessories', $updatedData['vehicle_id']));

        $this->assertDatabaseHas('vehicle_documents', [
            'name' => $updatedData['name'],
            'issue_date' => $updatedData['issue_date'],
            'expiration_date' => $updatedData['expiration_date'],
            'vehicle_id' => $updatedData['vehicle_id'],
            'data' => $updatedData['data'] != [] ? json_encode($updatedData['data']) : null,
        ]);    
    }

    public function test_vehicle_document_data_fails_validation_on_edit(): void
    {
        $issueDate = Carbon::instance(fake()->dateTime());
        $vehicleDocument = VehicleDocument::factory()->create();

        // Fail on empty key
        $data_1 = [];
        $key_1 = '';
        $value_1 = 'asd';
        $data_1[$key_1] = $value_1;

        $updatedData_1 = [
            'name' => fake()->name(),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiration_date' => $issueDate->copy()->addYear(1)->format('Y-m-d'),
            'vehicle_id' => $vehicleDocument->vehicle_id,
            'data' => $data_1,
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('vehicleDocuments.edit', $vehicleDocument->id), $updatedData_1);

        $response->assertSessionHasErrors(['data']);

        $this->assertDatabaseMissing('vehicle_documents', [
            'name' => $updatedData_1['name'],
            'issue_date' => $updatedData_1['issue_date'],
            'expiration_date' => $updatedData_1['expiration_date'],
            'vehicle_id' => $updatedData_1['vehicle_id'],
            'data' => json_encode($updatedData_1['data']),
        ]);

        // Fail on empty key
        $data_2 = [];
        $key_2 = '';
        $value_2 = 'asd';
        $data_2[$key_2] = $value_2;

        $updatedData_2 = [
            'name' => fake()->name(),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiration_date' => $issueDate->copy()->addYear(1)->format('Y-m-d'),
            'vehicle_id' => $vehicleDocument->vehicle_id,
            'data' => $data_2,
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('vehicleDocuments.edit', $vehicleDocument->id), $updatedData_2);

        $response->assertSessionHasErrors(['data']);

        $this->assertDatabaseMissing('vehicle_documents', [
            'name' => $updatedData_2['name'],
            'issue_date' => $updatedData_2['issue_date'],
            'expiration_date' => $updatedData_2['expiration_date'],
            'vehicle_id' => $updatedData_2['vehicle_id'],
            'data' => json_encode($updatedData_2['data']),
        ]);
    }

    public function test_user_can_delete_a_vehicle_document(): void
    {
        $vehicleDocument = VehicleDocument::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->delete(route('vehicleDocuments.delete', $vehicleDocument->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.documentsAndAccessories', $vehicleDocument->vehicle->id));

        $this->assertDatabaseMissing('vehicle_documents', [
            'id' => $vehicleDocument->id,
        ]);
    }

    public function test_vehicle_creation_handles_exception()
    {
        $issueDate = Carbon::instance(fake()->dateTime());

        $data = [
            'name' => fake()->name(),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiration_date' => $issueDate->copy()->addYear(1)->format('Y-m-d'),
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
            ->post(route('vehicleDocuments.create'), $data);

        // Assert: Check if the catch block was executed
        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.documentsAndAccessories',  $data['vehicle_id']));
    }
}
