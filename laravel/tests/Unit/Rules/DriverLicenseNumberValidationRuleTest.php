<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Rules\DriverLicenseNumberValidation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DriverLicenseNumberValidationRuleTest extends TestCase
{
    use RefreshDatabase;

    protected $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userId = User::factory()->create()->id;
    }

    public function test_fails_if_the_driver_license_prefix_is_invalid()
    {
        $rule = new DriverLicenseNumberValidation($this->userId);
        
        // Test with an invalid prefix
        $result = $rule->validate('license_number', 'ZZ-123456', function ($message) {
            $this->assertEquals(
                'Letras iniciais inseridas são inválidas. Só são permitidos estes conjuntos, correspondes à região portuguesa que emitiu a carta:
                AV (Aveiro), BE (Beja), BR (Braga), BG (Bragança), CB (Castelo Branco), C (Coimbra), E (Évora), FA (Faro), GD (Guarda), LE (Leiria), L (Lisboa), PT (Portalegre), 
                P (Porto), SA (Santarém), SE (Setúbal), VC (Viana do Castelo), VR (Vila Real), VS (Viseu), AN (Angra do Heroísmo), H (Horta), A (Ponta Delgada), M (Funchal)',
                $message
            );
        });
    }

    public function test_passes_if_the_driver_license_prefix_is_valid()
    {
        $rule = new DriverLicenseNumberValidation($this->userId);

        // Test with a valid prefix
        $result = $rule->validate('license_number', 'AV-123456', function ($message) {
            // This callback shouldn't be called
            $this->fail('The validation should have passed.');
        });

        $this->assertTrue(true); // If no exception is thrown, the test passes.
    }

    public function test_fails_if_the_driver_license_number_is_not_unique()
    {
        DB::shouldReceive('table->where->whereNot->exists')
            ->once()
            ->andReturn(true); // Simulate the license number existing for another user
        
        $rule = new DriverLicenseNumberValidation($this->userId);
        
        $result = $rule->validate('license_number', 'AV-123456', function ($message) {
            $this->assertEquals('Este número de carta já está associado a outro condutor', $message);
        });
    }

    public function test_passes_if_the_driver_license_number_is_unique_for_the_user()
    {
        DB::shouldReceive('table->where->whereNot->exists')
            ->once()
            ->andReturn(false); // Simulate the license number is unique

        $rule = new DriverLicenseNumberValidation($this->userId);

        $result = $rule->validate('license_number', 'AV-123456', function ($message) {
            // This callback shouldn't be called
            $this->fail('The validation should have passed.');
        });

        $this->assertTrue(true); // If no exception is thrown, the test passes.
    }
}
