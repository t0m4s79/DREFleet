<?php

namespace Tests\Feature;

use App\Enums\Roles;
use Database\Factories\UserFactory;
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
        $this->user = User::factory()->create(['user_type' => Roles::ADMIN->value]);
    }

    public function test_managers_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('managers.index'));

        $response->assertOk();
    }

    public function test_managers_page_is_not_displayed_due_permissions(): void
    {
        $this->assertForbiddenForNonAdmins('managers.index', null, true);
    }

    public function test_manager_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('managers.showCreate'));

        $response->assertOk();
    }

    public function test_manager_creation_page_is_not_displayed_due_permissions(): void
    {
        $this->assertForbiddenForNonAdmins('managers.showCreate');
    }

    public function test_manager_edit_page_is_displayed(): void
    {
        $manager = ManagerFactory::new()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('managers.showEdit', $manager->id));

        $response->assertOk();
    }

    public function test_manager_edit_page_is_not_displayed_due_permissions(): void
    {
        $manager = ManagerFactory::new()->create();

        $this->assertForbiddenForNonAdmins('managers.showEdit', $manager->id, true);
    }

    public function test_manager_approved_orders_page_is_displayed(): void
    {
        $manager = ManagerFactory::new()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('managers.approved', $manager->id));

        $response->assertOk();
    }

    public function test_manager_approved_orders_page_is_not_displayed_due_permissions(): void
    {
        $manager = ManagerFactory::new()->create();

        $this->assertForbiddenForNonAdmins('managers.approved', $manager->id, true);
    }

    public function test_user_can_create_a_manager(): void
    {
        $user = User::factory()->create();

        $managerData = [
            'id' => $user->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('managers.create'), $managerData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('managers.index'));


        $this->assertDatabaseHas('users', $managerData);
    }

    public function test_user_cannot_create_a_manager_due_permissions(): void
    {
        $technician = UserFactory::new()->create(['user_type' => Roles::TECHNICIAN->value]);
        $manager = UserFactory::new()->create(['user_type' => Roles::MANAGER->value]);
        $driver = UserFactory::new()->create(['user_type' => Roles::DRIVER->value]);
        $none = UserFactory::new()->create(['user_type' => Roles::NONE->value]);

        $users = [$technician, $manager, $driver, $none];

        foreach ($users as $user) {
            $newManager = User::factory()->create();

            $managerData = [
                'id' => $newManager->id,
                'user_type' => Roles::NONE->value
            ];

            $response = $this
                ->actingAs($user)
                ->post(route('managers.create'), $managerData);

            $response->assertForbidden();

            $this->assertDatabaseMissing('users', [
                'id' => $newManager->id,
                'user_type' => Roles::MANAGER->value,
            ]);
        }
    }

    public function test_create_manager_fails_on_user_type_is_not_none(): void
    {
        $user = User::factory()->create([
            'user_type' => Arr::random([Roles::TECHNICIAN->value, Roles::DRIVER->value, Roles::ADMIN->value]),
        ]);

        $managerData = [
            'id' => $user->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('managers.create'), $managerData);

        $response->assertSessionHasErrors(['id']);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'user_type' => Roles::MANAGER->value,
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
            ->put(route('managers.edit', $manager->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('managers.index'));

        $this->assertDatabaseHas('users', $updatedData);
    }

    public function test_user_cannot_edit_a_manager_due_permissions(): void
    {
        $manager = ManagerFactory::new()->create();

        $updatedData = [
            'name' => fake()->name(),
            'phone' => rand(910000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido']),
        ];

        $technician = UserFactory::new()->create(['user_type' => Roles::TECHNICIAN->value]);
        $driver = UserFactory::new()->create(['user_type' => Roles::DRIVER->value]);
        $none = UserFactory::new()->create(['user_type' => Roles::NONE->value]);

        $users = [$technician, $driver, $none];

        foreach ($users as $user) {
            $response = $this
                ->actingAs($user)
                ->put(route('managers.edit', $manager->id), $updatedData);

            $response->assertForbidden();

            $this->assertDatabaseMissing('users', $updatedData);
        }
    }

    public function test_user_can_delete_a_manager(): void
    {
        $manager = ManagerFactory::new()->create();

        $this->assertDatabaseHas('users', [
            'id' => $manager->id,
            'user_type' => Roles::MANAGER->value
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete(route('managers.delete', $manager->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('managers.index'));

        $this->assertDatabaseHas('users', [
            'id' => $manager->id,
            'user_type' => 'Nenhum'
        ]);
    }

    public function test_user_cannot_delete_a_manager_due_permissions(): void
    {
        $manager = ManagerFactory::new()->create();

        $this->assertDatabaseHas('users', [
            'id' => $manager->id,
            'user_type' => Roles::MANAGER->value
        ]);

        $technician = UserFactory::new()->create(['user_type' => Roles::TECHNICIAN->value]);
        $manager = UserFactory::new()->create(['user_type' => Roles::MANAGER->value]);
        $driver = UserFactory::new()->create(['user_type' => Roles::DRIVER->value]);
        $none = UserFactory::new()->create(['user_type' => Roles::NONE->value]);

        $users = [$technician, $manager, $driver, $none];

        foreach ($users as $user) {
            $response = $this
                ->actingAs($user)
                ->delete(route('admins.delete', $manager->id));

            $response->assertForbidden();

            $this->assertDatabaseMissing('users', [
                'id' => $manager->id,
                'user_type' => Roles::NONE->value
            ]);

            $this->assertDatabaseHas('users', [
                'id' => $manager->id,
                'user_type' => Roles::MANAGER->value
            ]);
        }
    }

    public function test_manager_creation_handles_exception()
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

        // Act: Send a POST request to the create manager route
        $response = $this
            ->actingAs($this->user)
            ->post(route('managers.create'), $data);

        // Assert: Check if the catch block was executed
        $response->assertRedirect(route('managers.index')); // Ensure it redirects back to the form
    }

    /**
     * Auxiliar method to verify if non-admin users cannot access some pages
     * @param string $route
     * @param mixed $id
     * @param bool $managerCanView
     * @return void
     */
    private function assertForbiddenForNonAdmins(string $route, ?int $id = null, ?bool $managerCanView = false): void
    {
        $technician = UserFactory::new()->create(['user_type' => Roles::TECHNICIAN->value]);
        $manager = UserFactory::new()->create(['user_type' => Roles::MANAGER->value]);
        $driver = UserFactory::new()->create(['user_type' => Roles::DRIVER->value]);
        $none = UserFactory::new()->create(['user_type' => Roles::NONE->value]);

        $users = [$technician, $driver, $none];

        if (!$managerCanView) {
            array_push($users, $manager);
        }

        foreach ($users as $user) {
            $response = $this
                ->actingAs($user)
                ->get(route($route, $id ? [$id] : []));

            $response->assertForbidden();
        }
    }

}