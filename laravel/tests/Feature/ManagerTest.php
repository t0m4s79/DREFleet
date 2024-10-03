<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Arr;
use Database\Factories\ManagerFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManagerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_managers_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/managers');

        $response->assertOk();
    }

    public function test_manager_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/managers/create');

        $response->assertOk();
    }

    public function test_manager_edit_page_is_displayed(): void
    {
        $manager = ManagerFactory::new()->create();

        $response = $this
            ->actingAs($this->user)
            ->get("/managers/showApproved/{$manager->id}");

        $response->assertOk();
    }

    public function test_manager_approved_orders_page_is_displayed(): void
    {
        $manager = ManagerFactory::new()->create();

        $response = $this
            ->actingAs($this->user)
            ->get("/managers/edit/{$manager->id}");

        $response->assertOk();
    }

    public function test_user_can_create_a_manager(): void
    {
        $user = User::factory()->create();

        $managerData = [
            'id' => $user->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/managers/create', $managerData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/managers');


        $this->assertDatabaseHas('users', $managerData);
    }

    public function test_create_manager_fails_on_non_none_user_type(): void
    {
        $user = User::factory()->create([
            'user_type' => Arr::random(['Técnico', 'Condutor', 'Administrador']),
        ]);

        $managerData = [
            'id' => $user->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/managers/create', $managerData);

        $response->assertSessionHasErrors(['id']);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'user_type' => 'Gestor',
        ]);
    }

    public function test_user_can_edit_a_manager(): void
    {
        $manager = ManagerFactory::new()->create();
    
        $updatedData = [
            'name' => fake()->name(),
            'phone' => rand(910000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido']),
        ]; 
        
        $response = $this
            ->actingAs($this->user)
            ->put("/managers/edit/{$manager->id}", $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/managers');

        $this->assertDatabaseHas('users', $updatedData);
    }

    public function test_user_can_delete_a_manager(): void
    {
        $manager = ManagerFactory::new()->create();

        $this->assertDatabaseHas('users', [
            'id' => $manager->id,
            'user_type' => 'Gestor'
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/managers/delete/{$manager->id}");

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/managers');

        $this->assertDatabaseHas('users', [
            'id' => $manager->id,
            'user_type' => 'Nenhum'
        ]);
    }

    public function test_manager_creation_handles_exception()
    {
        $user = User::factory()->create();

        $incomingFields = [
            'id' => $user->id,
        ];

        // Mock the User model to throw an exception
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create manager route
        $response = $this
            ->actingAs($this->user)
            ->post('/managers/create', $incomingFields);

        // Assert: Check if the catch block was executed
        $response->assertRedirect('/managers'); // Ensure it redirects back to the form
    }

}
