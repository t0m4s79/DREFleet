<?php

namespace Tests\Feature;

use App\Models\Kid;
use Tests\TestCase;
use App\Models\User;
use App\Models\KidEmail;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KidEmailTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['user_type' => 'Administrador']);
    }
    
    public function test_email_belongs_to_kid(): void
    {
        $kid = Kid::factory()->create();

        $kidEmail = KidEmail::factory()->create([
            'kid_id' => $kid->id,
        ]);

        $this->assertTrue($kidEmail->kid->is($kid));
    }

    public function test_kid_email_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('kidEmails.showCreate'));

        $response->assertOk();
    }

    public function test_kid_email_edit_page_is_displayed(): void
    {
        $kidEmail = KidEmail::factory()->create([
            'kid_id' => Kid::factory()->create(),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get(route('kidEmails.showEdit', $kidEmail->id));

        $response->assertOk();
    }

    public function test_user_can_create_a_kid_email(): void
    {
        $kidEmailData = [
            'email' => fake()->email(),
            'owner_name' => fake()->name(),
            'relationship_to_kid' => Arr::random(['Avô', 'Avó', 'Pai', 'Mãe', 'Primo', 'Tia', 'Tio', 'Tutor']),
            'preference' => Arr::random(['Alternativa', 'Preferida']),
            'kid_id' => Kid::factory()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('kidEmails.create'), $kidEmailData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kids.contacts', $kidEmailData['kid_id']));

        $this->assertDatabaseHas('kid_emails', $kidEmailData);
    }

    public function test_user_can_edit_a_kid_email(): void
    {
        $kidEmail = KidEmail::factory()->create([
            'kid_id' => Kid::factory()->create(),
        ]);
    
        $updatedData = [
            'email' => fake()->email(),
            'owner_name' => fake()->name(),
            'relationship_to_kid' => Arr::random(['Avô', 'Avó', 'Pai', 'Mãe', 'Primo', 'Tia', 'Tio', 'Tutor']),
            'preference' => Arr::random(['Alternativa', 'Preferida']),
            'kid_id' => Kid::factory()->create()->id,
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put(route('kidEmails.edit', $kidEmail->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kids.contacts', $updatedData['kid_id']));

        $this->assertDatabaseHas('kid_emails', $updatedData); 
    }

    public function test_user_can_delete_a_kid_email(): void
    {
        $kidEmail = KidEmail::factory()->create([
            'kid_id' => Kid::factory()->create(),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete(route('kidEmails.delete', $kidEmail->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kids.contacts', $kidEmail->kid->id));


        $this->assertDatabaseMissing('kid_emails', [
            'id' => $kidEmail->id,
        ]);
    }
}