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
            'date' => 'Data encontr-se num formato inválido',
            'driver_id.exists' => 'Condutor selecionado não existe',
            'email' => 'Este campo só permite emails',
            'id.exists' => 'O utilizador especificado não existe',
            'in' => 'O valor do campo não corresponde aos valores permitidos',
            'integer' => 'Apenas são permitidos números neste campo',
            'json' => 'Formato inválido (json)',
            'latitude.regex' => 'Este campo só permite 10 casas decimais',
            'longituder.regex' => 'Este campo só permite 10 casas decimais',
            'max' => 'O campo não pode ultrapassar :max',
            'max' => 'O campo deve ter no máximo :max caracteres',
            'min' => 'O campo deve ser pelo menos :min',
            'numeric' => 'Apenas são permitidos números neste campo',
            'required' => 'Este campo é obrigatório',
            'status.in' => 'Este campo contém valores fora dos permitidos',
            'string' => 'Formato inválido (string)',
            'technician_id.exists' => 'Técnico selecionado não existe',
            'user_id.exists' => 'O utilizador especificado não existe',
            'vehicle_id.exists' => 'Veículo selecionado não existe',

            // Drivers
            'heavy_license_type.in' => 'Este campo contém valores fora dos permitidos',
            'heavy_license_type.required_if' => 'Tipo de Carta deve ser especificado caso o condutor tenha carta de pesados',

            // Email
            'email.email' => 'O campo e-mail deve ser um endereço de e-mail válido',
            'email.unique' => 'Este endereço de e-mail já está em uso',

            // Password
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
            'capacity.integer' => 'O campo capacidade deve ser um número inteiro',
            'capacity.min' => 'A capacidade deve ser no mínimo :min',
            'current_kilometrage.integer' => 'O campo kilometragem atual deve ser um número inteiro',
            'current_kilometrage.min' => 'A kilometragem atual deve ser no mínimo :min',
            'current_month_fuel_requests.integer' => 'O campo pedidos de reabastecimento deve ser um número inteiro',
            'fuel_consumption.min' => 'O campo consumo deve ser no mínimo :min',
            'heavy_type.required_if' => 'Tipo de Pesado deve ser especificado caso o veículo seja pesado',
            'heavy_vehicle.boolean' => 'O campo veículo pesado deve ser verdadeiro ou falso',
            'license_plate.regex' => 'A matrícula deve ter no mínimo 2 letras e pode ter até 6 caracteres, aceitando apenas letras e números',
            'license_plate.unique' => 'Já existe um veículo com esta matrícula',
            'wheelchair_adapted.boolean' => 'O campo adaptação para cadeiras de rodas deve ser verdadeiro ou falso',
            'year.digits' => 'O campo ano deve ter 4 dígitos',
            'year.integer' => 'O campo ano deve ser um número inteiro',

        ];
    }
}