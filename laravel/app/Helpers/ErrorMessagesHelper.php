<?php


namespace App\Helpers;

class ErrorMessagesHelper
{
    public static function getErrorMessages()
    {
        return [
            'required' => 'Este campo é obrigatório.',
            'user_id.unique' => 'Este utilizador já é um condutor.',
            // Phone
            'phone.required' => 'O campo telefone é obrigatório.',
            'phone.numeric' => 'O campo telefone deve conter apenas números.',
            'phone.digits_between' => 'O campo telefone deve ter entre 9 e 15 dígitos.',
            'phone.regex' => 'O campo telefone deve ter entre 9 e 15 dígitos.',
            'phone.unique' => 'Este número de telefone já está em uso.',
            'numeric' => 'Apenas são permitidos números.',
            // Places
            'latitude.between' => 'A latitude deve estar entre -90 e 90 graus.',
            'longitude.between' => 'A longitude deve estar entre -180 e 180 graus.',
            'latitude.regex' => 'O formato da latitude é inválido. Deve ter 1 a 10 casas decimais.',
            'longitude.regex' => 'O formato da longitude é inválido. Deve ter 1 a 10 casas decimais.',
            'known_as.regex' => 'O campo "Conhecido como" deve conter apenas letras e espaços.',
            // Name
            'name.required' => 'O campo nome é obrigatório.',
            'name.max' => 'O campo nome não pode mais de 255 carateres',
            // Email
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'O campo e-mail deve ser um endereço de e-mail válido.',
            'email.unique' => 'Este endereço de e-mail já está em uso.',
            // Password
            'password.required' => 'O campo senha é obrigatório.',
            'password.confirmed' => 'As senhas não coincidem.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.mixed_case' => 'A senha deve conter pelo menos uma letra maiúscula e uma letra minúscula.',
            'password.numbers' => 'A senha deve conter pelo menos um número.',
            'password.symbols' => 'A senha deve conter pelo menos um caracter especial.',
            'password.confirmed' => 'As senhas não coincidem.',
            // Vehicles
            'license_plate.unique' => 'Já existe um veículo com esta matrícula.',
            'license_plate.regex' => 'A matrícula deve ter no mínimo 2 letras e pode ter até 6 caracteres, aceitando apenas letras e números.',
            'year.integer' => 'O campo ano deve ser um número inteiro.',
            'year.digits' => 'O campo ano deve ter 4 dígitos.',
            'heavy_vehicle.boolean' => 'O campo veículo pesado deve ser verdadeiro ou falso.',
            'wheelchair_adapted.boolean' => 'O campo adaptação para cadeiras de rodas deve ser verdadeiro ou falso.',
            'capacity.integer' => 'O campo capacidade deve ser um número inteiro.',
            'capacity.min' => 'A capacidade deve ser no mínimo :min.',
            'fuel_consumption.numeric' => 'O campo consumo deve ser um número.',
            'fuel_consumption.min' => 'O campo consumo deve ser no mínimo :min.',
            'status.in' => 'O campo estado deve ser um dos seguintes: ativo, inativo, manutenção.',
            'current_month_fuel_requests.integer' => 'O campo pedidos de reabastecimento deve ser um número inteiro.',
        ];
    }
}