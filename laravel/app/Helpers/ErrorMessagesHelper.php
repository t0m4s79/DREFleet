<?php


namespace App\Helpers;

class ErrorMessagesHelper
{
    public static function getErrorMessages()
    {
        return [
            // Not Specific
            'array' => 'Formato inválido (array)',
            'boolean' => 'Este campo só permite valores que que representem verdadeiro (1) ou falso (0)',
            'between' => 'O valor deve estar entre :min e :max',
            'date' => 'Data encontra-se num formato inválido',
            'digits' => 'O campo deve ter exatamente :digits dígitos.',
            'driver_id.exists' => 'Condutor selecionado não existe',
            'email' => 'Este campo só permite emails',
            'id.exists' => 'O utilizador especificado não existe',
            'in' => 'O valor do campo não corresponde aos valores permitidos',
            'integer' => 'Apenas são permitidos números neste campo',
            'json' => 'Formato inválido (json)',
            'latitude.regex' => 'Este campo vai de -90 a 90 e permite até 15 casas decimais',
            'longitude.regex' => 'Este campo vai de -180 a 180 e permite até 15 casas decimais',
            'max' => 'O campo não pode ultrapassar :max caracteres',
            'min' => 'O campo deve ter pelo menos :min caracteres',
            'numeric' => 'Apenas são permitidos números neste campo',
            'required' => 'Este campo é obrigatório',
            'status.in' => 'Este campo contém valores não permitidos',
            'string' => 'Formato inválido (string)',
            'technician_id.exists' => 'Técnico selecionado não existe',
            'user_id.exists' => 'O utilizador especificado não existe',
            'vehicle_id.exists' => 'Veículo selecionado não existe',

            // Drivers
            'heavy_license_type.in' => 'Este campo contém valores fora dos permitidos',
            'heavy_license_type.required_if' => 'Tipo de Carta deve ser especificado caso o condutor tenha carta de pesados',

            // Email
            'email.email' => 'Este campo deve ser um endereço email válido',
            'email.unique' => 'Este endereço email já está em uso',
            'email.lowercase' => 'Este campo só permite letras minúsculas',

            // Password
            'current_password.current_password' => 'A password introduzida está incorreta',
            'password.confirmed' => 'As senhas não coincidem',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres',
            'password.mixed_case' => 'A senha deve conter pelo menos uma letra maiúscula e uma letra minúscula',
            'password.numbers' => 'A senha deve conter pelo menos um número',
            'password.symbols' => 'A senha deve conter pelo menos um caracter especial',

            // Places
            'known_as.regex' => 'O campo "Conhecido como" deve conter apenas letras e espaços',

            // Phone
            'phone.digits_between' => 'O campo telefone deve ter entre 9 e 15 dígitos',
            'phone.unique' => 'Este número de telefone já está em uso',
            'phone.regex' => 'O campo telefone deve ter entre 9 e 15 dígitos',

            // Vehicles
            'heavy_type.required_if' => 'Tipo de Pesado deve ser especificado caso o veículo seja pesado',
            'license_plate.regex' => 'A matrícula deve ter no mínimo 2 letras e pode ter até 6 caracteres, aceitando apenas letras e números',
            'license_plate.unique' => 'Já existe um veículo com esta matrícula',
        ];
    }
}