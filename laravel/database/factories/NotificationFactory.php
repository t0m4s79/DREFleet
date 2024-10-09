<?php

namespace Database\Factories;

use App\Models\Kid;
use App\Models\User;
use App\Models\Order;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        // Randomly choose the related entity type
        $type = $this->faker->randomElement(['Veículo', 'Pedido', 'Utilizador', 'Condutor', 'Criança']);
        
        // Determine the related entity ID and type based on the selected entity type
        switch ($type) {
            case 'Veículo':
                $relatedEntityId = Vehicle::factory()->create()->id;
                $relatedEntityType = Vehicle::class;
                break;
            case 'Pedido':
                $relatedEntityId = Order::factory()->create()->id;
                $relatedEntityType = Order::class;
                break;
            case 'Utilizador':
                $relatedEntityId = User::factory()->create()->id;
                $relatedEntityType = User::class;
                break;
            case 'Condutor':
                $relatedEntityId = Driver::factory()->create()->user_id;
                $relatedEntityType = Driver::class;
                break;
            case 'Criança':
                $relatedEntityId = Kid::factory()->create()->id;
                $relatedEntityType = Kid::class;
                break;
        }

        return [
            'user_id' => User::factory(),  // The user who receives the notification
            'related_entity_type' => $relatedEntityType,
            'related_entity_id' => $relatedEntityId,
            'type' => $type,
            'title' => $this->faker->sentence,
            'message' => $this->faker->text,
            'is_read' => $this->faker->boolean,
        ];
    }
}
