<?php

namespace Tests\Feature;

use App\Models\Kid;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Arr;
use App\Models\KidPhoneNumber;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KidPhoneNumberTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['user_type' => 'Administrador']);
    }
    
    public function test_phone_number_belongs_to_kid(): void
    {
        $kid = Kid::factory()->create();

        $kidPhoneNumber = KidPhoneNumber::factory()->create([
            'kid_id' => $kid->id,
        ]);

        $this->assertTrue($kidPhoneNumber->kid->is($kid));
    }

    public function test_kid_phone_number_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('kidPhoneNumbers.showCreate'));

        $response->assertOk();
    }

    public function test_kid_phone_number_edit_page_is_displayed(): void
    {
        $kidPhoneNumber = KidPhoneNumber::factory()->create([
            'kid_id' => Kid::factory()->create(),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get(route('kidPhoneNumbers.showEdit', $kidPhoneNumber->id));

        $response->assertOk();
    }

    public function test_user_can_create_a_kid_phone_number(): void
    {
        $kidPhoneNumberData = [
            'phone' => rand(910000000,929999999),
            'owner_name' => fake()->name(),
            'relationship_to_kid' => Arr::random(['Avô', 'Avó', 'Pai', 'Mãe', 'Primo', 'Tia', 'Tio', 'Tutor']),
            'preference' => Arr::random(['Alternativa', 'Preferida']),
            'kid_id' => Kid::factory()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('kidPhoneNumbers.create'), $kidPhoneNumberData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kids.contacts', $kidPhoneNumberData['kid_id']));

        $this->assertDatabaseHas('kid_phone_numbers', $kidPhoneNumberData);
    }

    public function test_user_can_edit_a_kid_phone_number(): void
    {
        $kidPhoneNumber = KidPhoneNumber::factory()->create([
            'kid_id' => Kid::factory()->create(),
        ]);
    
        $updatedData = [
            'phone' => rand(910000000,929999999),
            'owner_name' => fake()->name(),
            'relationship_to_kid' => Arr::random(['Avô', 'Avó', 'Pai', 'Mãe', 'Primo', 'Tia', 'Tio', 'Tutor']),
            'preference' => Arr::random(['Alternativa', 'Preferida']),
            'kid_id' => Kid::factory()->create()->id,
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put(route('kidPhoneNumbers.edit', $kidPhoneNumber->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kids.contacts', $updatedData['kid_id']));
            

        $this->assertDatabaseHas('kid_phone_numbers', $updatedData); 
    }

    public function test_user_can_delete_a_kid_phone_number(): void
    {
        $kidPhoneNumber = KidPhoneNumber::factory()->create([
            'kid_id' => Kid::factory()->create(),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete(route('kidPhoneNumbers.delete', $kidPhoneNumber->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kids.contacts', $kidPhoneNumber->kid->id));


        $this->assertDatabaseMissing('kid_phone_numbers', [
            'id' => $kidPhoneNumber->id,
        ]);
    }
}