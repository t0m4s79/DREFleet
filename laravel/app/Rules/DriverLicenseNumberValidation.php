<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DriverLicenseNumberValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
         $prefix = explode('-', $value)[0];
        
         $allowedPrefixes = ['AV','BE','BR','BG','CB','C','E','FA','GD','LE','L','PT','P','SA','SE','VC','VR','VS','AN','H','A','M'];

         if (!in_array($prefix, $allowedPrefixes)) {
             $fail('Letras iniciais inseridas são inválidas. Só são permitidos estes conjuntos, correspondes à região portuguesa que emitiu a carta:
                    AV (Aveiro), BE (Beja), BR (Braga), BG (Bragança), CB (Castelo Branco), C (Coimbra), E (Évora), FA (Faro), GD (Guarda), LE (Leiria), L (Lisboa), PT (Portalegre), 
                    P (Porto), SA (Santarém), SE (Setúbal), VC (Viana do Castelo), VR (Vila Real), VS (Viseu), AN (Angra do Heroísmo), H (Horta), A (Ponta Delgada), M (Funchal)',
                );
         }
    }
}
