<?php

namespace Tests\Feature;

use App\Enums\Roles;
use Database\Factories\AdminFactory;
use Database\Factories\DriverFactory;
use Database\Factories\ManagerFactory;
use Database\Factories\TechnicianFactory;
use Database\Factories\UserFactory;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['user_type' => Roles::ADMIN->value]);
    }

    public function test_admins_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('admins.index'));

        $response->assertOk();
    }

    public function test_admins_page_is_not_displayed_due_permissions(): void
    {
        $this->assertForbiddenForNonAdmins('admins.index');
    }

    public function test_admin_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('admins.showCreate'));

        $response->assertOk();
    }

    public function test_admin_creation_page_is_not_displayed_due_permissions(): void
    {
        $this->assertForbiddenForNonAdmins('admins.showCreate');
    }

    public function test_admin_edit_page_is_displayed(): void
    {
        $admin = AdminFactory::new()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('admins.showEdit', $admin->id));

        $response->assertOk();
    }

    public function test_admin_edit_page_is_not_displayed_due_permissions(): void
    {
        $admin = AdminFactory::new()->create();
        $this->assertForbiddenForNonAdmins('admins.showEdit', $admin->id);
    }

    public function test_user_can_create_an_admin(): void
    {
        $user = User::factory()->create();

        $adminData = [
            'id' => $user->id,
            'user_type' => Roles::NONE->value
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('admins.create'), $adminData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admins.index'));


        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'user_type' => Roles::ADMIN->value,
        ]);
    }

    public function test_user_cannot_create_an_admin_due_permissions(): void
    {
        $technician = UserFactory::new()->create(['user_type' => Roles::TECHNICIAN->value]);
        $manager = UserFactory::new()->create(['user_type' => Roles::MANAGER->value]);
        $driver = UserFactory::new()->create(['user_type' => Roles::DRIVER->value]);

        $users = [$technician, $manager, $driver];

        foreach ($users as $user) {
            $adminData = ['id' => $user->id, 'user_type' => Roles::NONE->value];

            $response = $this
                ->actingAs($user)
                ->post(route('admins.create'), $adminData);

            $response->assertForbidden();

            $this->assertDatabaseMissing('users', [
                'id' => $user->id,
                'user_type' => Roles::ADMIN->value,
            ]);
        }
    }

    public function test_create_admins_fails_on_user_type_is_not_none(): void
    {
        $user = User::factory()->create([
            'user_type' => Roles::MANAGER->value,
        ]);

        $adminData = [
            'id' => $user->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('admins.create'), $adminData);

        $response->assertSessionHasErrors(['id']);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'user_type' => Roles::ADMIN->value,
        ]);
    }

    public function test_user_can_edit_an_admin(): void
    {
        $admin = AdminFactory::new()->create();

        $updatedData = [
            'name' => fake()->name(),
            'phone' => rand(910000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido']),
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('admins.edit', $admin->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admins.index'));

            $this->assertDatabaseHas('users', array_merge(['id' => $admin->id], $updatedData));
    }

    public function test_user_cannot_edit_an_admin_due_permissions(): void
    {
        $admin = AdminFactory::new()->create();

        $updatedData = [
            'name' => fake()->name(),
            'phone' => rand(910000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido']),
        ];

        $technician = UserFactory::new()->create(['user_type' => Roles::TECHNICIAN->value]);
        $manager = UserFactory::new()->create(['user_type' => Roles::MANAGER->value]);
        $driver = UserFactory::new()->create(['user_type' => Roles::DRIVER->value]);

        $users = [$technician, $manager, $driver];

        foreach ($users as $user) {
            $response = $this
                ->actingAs($user)
                ->put(route('admins.edit', $admin->id), $updatedData);

            $response->assertForbidden();

            $this->assertDatabaseMissing('users', $updatedData);
        }
    }

    public function test_user_can_delete_an_admin(): void
    {
        $admin = AdminFactory::new()->create();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'user_type' => Roles::ADMIN->value
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete(route('admins.delete', $admin->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admins.index'));

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'user_type' => Roles::NONE->value
        ]);
    }

    public function test_user_cannot_delete_an_admin_due_permissions(): void
    {
        $admin = AdminFactory::new()->create();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'user_type' => Roles::ADMIN->value
        ]);

        $technician = UserFactory::new()->create(['user_type' => Roles::TECHNICIAN->value]);
        $manager = UserFactory::new()->create(['user_type' => Roles::MANAGER->value]);
        $driver = UserFactory::new()->create(['user_type' => Roles::DRIVER->value]);

        $users = [$technician, $manager, $driver];

        foreach ($users as $user) {
            $response = $this
                ->actingAs($user)
                ->delete(route('admins.delete', $admin->id));

            $response->assertForbidden();

            $this->assertDatabaseMissing('users', [
                'id' => $admin->id,
                'user_type' => Roles::NONE->value
            ]);
        }
    }

    public function test_admin_creation_handles_exception()
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

        // Act: Send a POST request to the create admin route
        $response = $this
            ->actingAs($this->user)
            ->post(route('admins.create'), $data);

        // Assert: Check if the catch block was executed
        $response->assertRedirect(route('admins.index')); // Ensure it redirects back to the form
    }

    /**
     * Auxiliar method to verify if non-admin users cannot access some pages
     * @param string $route
     * @param mixed $id
     * @return void
     */
    private function assertForbiddenForNonAdmins(string $route, ?int $id = null): void
    {
        $technician = UserFactory::new()->create(['user_type' => Roles::TECHNICIAN->value]);
        $manager = UserFactory::new()->create(['user_type' => Roles::MANAGER->value]);
        $driver = UserFactory::new()->create(['user_type' => Roles::DRIVER->value]);

        $users = [$technician, $manager, $driver];

        foreach ($users as $user) {
            $response = $this
                ->actingAs($user)
                ->get(route($route, $id ? [$id] : []));

            $response->assertForbidden();
        }
    }
}
