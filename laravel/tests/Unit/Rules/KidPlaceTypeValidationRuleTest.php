<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Place;
use App\Rules\KidPlaceTypeValidation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KidPlaceTypeValidationRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_fails_if_any_place_is_not_of_type_residencia()
    {
        // Create places in the database
        $place1 = Place::factory()->create(['place_type' => 'Residência']);
        $place2 = Place::factory()->create(['place_type' => 'Escola']); // Invalid type

        $rule = new KidPlaceTypeValidation();
        $places = [$place1->id, $place2->id];

        $rule->validate('places', $places, function ($message) {
            $this->assertEquals('Apenas moradas com tipo "Residência" podem ser associadas a crianças', $message);
        });
    }

    public function test_fails_if_a_place_does_not_exist()
    {
        // Create a valid place
        $place = Place::factory()->create(['place_type' => 'Residência']);
        
        $rule = new KidPlaceTypeValidation();
        $places = [$place->id, 999]; // 999 doesn't exist

        $rule->validate('places', $places, function ($message) {
            $this->assertEquals('Apenas moradas com tipo "Residência" podem ser associadas a crianças', $message);
        });
    }

    public function test_passes_if_all_places_are_of_type_residencia()
    {
        // Create valid places
        $place1 = Place::factory()->create(['place_type' => 'Residência']);
        $place2 = Place::factory()->create(['place_type' => 'Residência']);

        $rule = new KidPlaceTypeValidation();
        $places = [$place1->id, $place2->id];

        $rule->validate('places', $places, function ($message) {
            $this->fail('The validation should have passed.');
        });

        $this->assertTrue(true); // If no exception is thrown, the test passes.
    }
}
