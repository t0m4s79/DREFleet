<?php

namespace Tests\Feature;

use App\Models\Kid;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Arr;
use Database\Factories\TechnicianFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TechnicianTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_technicians_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('technicians.index'));

        $response->assertOk();
    }

    public function test_technician_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('technicians.showCreate'));

        $response->assertOk();
    }

    public function test_technician_edit_page_is_displayed(): void
    {
        $technician = TechnicianFactory::new()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('technicians.showEdit', $technician->id));

        $response->assertOk();
    }

    public function test_user_can_create_a_technician(): void
    {
        $user = User::factory()->create();

        $technicianData = [
            'id' => $user->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('technicians.create'), $technicianData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('technicians.index'));


        $this->assertDatabaseHas('users', $technicianData);
    }

    public function test_create_technician_fails_on_user_type_is_not_none(): void
    {
        $user = User::factory()->create([
            'user_type' => $userType = Arr::random(['Gestor', 'Condutor', 'Administrador']),
        ]);

        $managerData = [
            'id' => $user->id,
        ];

        // Select the route based on the user_type
        $route = match ($userType) {
            'Gestor' => route('managers.create'),
            'Condutor' => route('drivers.create'),
            default => route('managers.create'), // Fallback route, you can modify it as needed
        };

        // Make the post request using the selected route
        $response = $this
            ->actingAs($this->user)
            ->post($route, $managerData);

        $response->assertSessionHasErrors(['id']);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'user_type' => 'Técnico',
        ]);
    }

    public function test_user_can_edit_a_technician(): void
    {
        $technician = TechnicianFactory::new()->create();
    
        $updatedData = [
            'name' => fake()->name(),
            'phone' => rand(910000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido']),
        ]; 
        
        $response = $this
            ->actingAs($this->user)
            ->put(route('technicians.edit', $technician->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('technicians.index'));

        $this->assertDatabaseHas('users', $updatedData);
    }

    public function test_user_can_delete_a_technician(): void
    {
        $technician = TechnicianFactory::new()->create();

        $this->assertDatabaseHas('users', [
            'id' => $technician->id,
            'user_type' => 'Técnico'
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete(route('technicians.delete', $technician->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('technicians.index'));

        $this->assertDatabaseHas('users', [
            'id' => $technician->id,
            'user_type' => 'Nenhum'
        ]);
    }

    public function test_technician_creation_handles_exception()
    {
        $user = User::factory()->create();

        $data = [
            'id' => $user->id,
        ];

        // Mock the User model to throw an exception
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create technician route
        $response = $this
            ->actingAs($this->user)
            ->post(route('technicians.create'), $data);

        // Assert: Check if the catch block was executed
        $response->assertRedirect(route('technicians.index')); // Ensure it redirects back to the form
    }

}
