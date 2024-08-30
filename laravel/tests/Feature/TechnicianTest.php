<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Database\Factories\TechnicianFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TechnicianTest extends TestCase
{
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
            ->get('/technicians');

        $response->assertOk();
    }

    public function test_technician_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/technicians/create');

        $response->assertOk();
    }

    public function test_technician_edit_page_is_displayed(): void
    {
        $technician = TechnicianFactory::new()->create();

        $response = $this
            ->actingAs($this->user)
            ->get("/technicians/edit/{$technician->id}");

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
            ->post('/technicians/create', $technicianData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/technicians');


        $this->assertDatabaseHas('users', $technicianData);
    }

    public function test_user_can_edit_a_technician(): void
    {
        $technician = TechnicianFactory::new()->create();
    
        $updatedData = [
            'id' => $technician->id,
            'name' => fake()->name(),
            'phone' => rand(910000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
            'status' => '1',
        ]; 
        
        $response = $this
            ->actingAs($this->user)
            ->put("/technicians/edit/{$technician->id}", $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/technicians');

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
            ->delete("/technicians/delete/{$technician->id}");

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/technicians');

        $this->assertDatabaseHas('users', [
            'id' => $technician->id,
            'user_type' => 'Nenhum'
        ]);
    }

    public function test_user_can_create_a_technician_with_kids():void
    {
        
    }

    public function test_user_edit_a_technician_and_their_kids():void
    {
        
    }


    public function test_technician_creation_handles_exception()
    {

    }

}
