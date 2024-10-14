<?php

namespace App\Rules;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\ValidationRule;

class DriverLicenseNumberValidation implements ValidationRule
{
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        // 1 - Check if format is correct
        $prefix = explode('-', $value)[0];
    
        $allowedPrefixes = ['AV','BE','BR','BG','CB','C','E','FA','GD','LE','L','PT','P','SA','SE','VC','VR','VS','AN','H','A','M'];

        if (!in_array($prefix, $allowedPrefixes)) {
            $fail('Letras iniciais inseridas são inválidas. Só são permitidos estes conjuntos, correspondes à região portuguesa que emitiu a carta:
                AV (Aveiro), BE (Beja), BR (Braga), BG (Bragança), CB (Castelo Branco), C (Coimbra), E (Évora), FA (Faro), GD (Guarda), LE (Leiria), L (Lisboa), PT (Portalegre), 
                P (Porto), SA (Santarém), SE (Setúbal), VC (Viana do Castelo), VR (Vila Real), VS (Viseu), AN (Angra do Heroísmo), H (Horta), A (Ponta Delgada), M (Funchal)',
            );
        }

        // 2 - Check if number already exists in database (must be unique)
        if (DB::table('drivers')->where('license_number', $value)->whereNot('user_id', $this->userId)->exists()) {
            $fail("Este número de carta já está associado a outro condutor");
        }

    }
}
