<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $heavyLicense = fake()->boolean();
        $heavyLicenseType = $heavyLicense ? Arr::random(['Mercadorias', 'Passageiros']) : null;

        return [
            'user_id' => User::factory()->state([
                'user_type' => 'Condutor',
            ]),
            'license_number' => $this->getRandomRegionIdentifier() . '-' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) . ' ' . rand(0, 9),
            'heavy_license' => $heavyLicense,
            'heavy_license_type' => $heavyLicenseType,
            'license_expiration_date' => fake()->date(now()->addYears(rand(1,5))),
        ];    
    }

    /*
        Aveiro - AV.
        Beja - BE.
        Braga - BR.
        Bragança - BG.
        Castelo Branco - CB.
        Coimbra - C.
        Évora - E.
        Faro - FA.
        Guarda - GD.
        Leiria - LE.
        Lisboa - L.
        Portalegre - PT.
        Porto - P.
        Santarém - SA.
        Setúbal - SE.
        Viana do Castelo - VC.
        Vila Real - VR.
        Viseu - VS.
        Angra do Heroísmo - AN.
        Horta - H.
        Ponta Delgada - A.
        Funchal - M.
    */
    private function getRandomRegionIdentifier() :string
    {
        return Arr::random(['AV','BE','BR','BG','CB','C','E','FA','GD','LE','L','PT','P','SA','SE','VC','VR','VS','AN','H','A','M']);
    }
}
