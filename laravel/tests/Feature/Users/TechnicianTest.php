<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Kid;
use Database\Factories\UserFactory;
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
        $this->user = User::factory()->create(['user_type' => Roles::ADMIN->value]);
    }

    public function test_technicians_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('technicians.index'));

        $response->assertOk();
    }

    public function test_technicians_page_is_not_displayed_due_permissions(): void
    {
        $this->assertForbiddenForUnauthorizedUsers('technicians.index');
    }

    public function test_technician_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('technicians.showCreate'));

        $response->assertOk();
    }

    public function test_technician_creation_page_is_not_displayed_due_permissions(): void
    {
        $this->assertForbiddenForUnauthorizedUsers('technicians.showCreate');
    }

    public function test_technician_edit_page_is_displayed(): void
    {
        $technician = TechnicianFactory::new()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('technicians.showEdit', $technician->id));

        $response->assertOk();
    }

    public function test_technician_edit_page_is_not_displayed_due_permissions(): void
    {
        $technician = TechnicianFactory::new()->create();

        $this->assertForbiddenForUnauthorizedUsers('technicians.showEdit', $technician->id);
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

    public function test_user_cannot_create_a_technician_due_permissions(): void
    {
        $driver = UserFactory::new()->create(['user_type' => Roles::DRIVER->value]);
        $none = UserFactory::new()->create(['user_type' => Roles::NONE->value]);

        $users = [$driver, $none];

        foreach ($users as $user) {
            $newTechnician = User::factory()->create();

            $technicianData = [
                'id' => $newTechnician->id,
                'user_type' => Roles::NONE->value
            ];

            $response = $this
                ->actingAs($user)
                ->post(route('technicians.create'), $technicianData);

            $response->assertForbidden();

            $this->assertDatabaseMissing('users', [
                'id' => $user->id,
                'user_type' => Roles::TECHNICIAN->value,
            ]);
        }
    }

    public function test_create_technician_fails_on_user_type_is_not_none(): void
    {
        $user = User::factory()->create([
            'user_type' => Roles::MANAGER->value,
        ]);

        $technicianData = [
            'id' => $user->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('technicians.create'), $technicianData);

        $response->assertSessionHasErrors(['id']);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'user_type' => Roles::TECHNICIAN->value,
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

    public function test_user_cannot_edit_a_technician_due_permissions(): void
    {
        $technician = TechnicianFactory::new()->create();

        $updatedData = [
            'name' => fake()->name(),
            'phone' => rand(910000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido']),
        ];

        $driver = UserFactory::new()->create(['user_type' => Roles::DRIVER->value]);
        $none = UserFactory::new()->create(['user_type' => Roles::NONE->value]);

        $users = [$driver, $none];

        foreach ($users as $user) {
            $response = $this
                ->actingAs($user)
                ->put(route('technicians.edit', $technician->id), $updatedData);

            $response->assertForbidden();

            $this->assertDatabaseMissing('users', $updatedData);
        }
    }

    public function test_user_can_delete_a_technician(): void
    {
        $technician = TechnicianFactory::new()->create();

        $this->assertDatabaseHas('users', [
            'id' => $technician->id,
            'user_type' => Roles::TECHNICIAN->value
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete(route('technicians.delete', $technician->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('technicians.index'));

        $this->assertDatabaseHas('users', [
            'id' => $technician->id,
            'user_type' => Roles::NONE->value
        ]);
    }

    public function test_user_cannot_delete_a_technician_due_permissions(): void
    {
        $technician = TechnicianFactory::new()->create();

        $driver = UserFactory::new()->create(['user_type' => Roles::DRIVER->value]);
        $none = UserFactory::new()->create(['user_type' => Roles::NONE->value]);

        $users = [$driver, $none];

        foreach ($users as $user) {
            $response = $this
                ->actingAs($user)
                ->delete(route('technicians.delete', $technician->id));

            $response->assertForbidden();

            $this->assertDatabaseMissing('users', [
                'id' => $technician->id,
                'user_type' => Roles::NONE->value
            ]);
        }
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

    /**
     * Auxiliar method to verify if driver and none users cannot access some pages
     * @param string $route
     * @param mixed $id
     * @return void
     */
    private function assertForbiddenForUnauthorizedUsers(string $route, ?int $id = null): void
    {
        $driver = UserFactory::new()->create(['user_type' => Roles::DRIVER->value]);
        $none = UserFactory::new()->create(['user_type' => Roles::NONE->value]);

        $users = [$driver, $none];

        foreach ($users as $user) {
            $response = $this
                ->actingAs($user)
                ->get(route($route, $id ? [$id] : []));

            $response->assertForbidden();
        }
    }

}