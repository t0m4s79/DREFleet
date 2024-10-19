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
                // Attempt to fetch an existing vehicle from the database, or create a new one if none exist
                $vehicle = Vehicle::inRandomOrder()->first();
                $relatedEntityId = $vehicle ? $vehicle->id : Vehicle::factory()->create()->id;
                $relatedEntityType = Vehicle::class;
                break;

            case 'Pedido':
                // Attempt to fetch an existing order from the database, or create a new one if none exist
                $order = Order::inRandomOrder()->first();
                $relatedEntityId = $order ? $order->id : Order::factory()->create()->id;
                $relatedEntityType = Order::class;
                break;

            case 'Utilizador':
                // Attempt to fetch an existing user from the database, or create a new one if none exist
                $user = User::inRandomOrder()->first();
                $relatedEntityId = $user ? $user->id : User::factory()->create()->id;
                $relatedEntityType = User::class;
                break;

            case 'Condutor':
                // Attempt to fetch an existing driver from the database, or create a new one if none exist
                $driver = Driver::inRandomOrder()->first();
                $relatedEntityId = $driver ? $driver->user_id : Driver::factory()->create()->user_id;
                $relatedEntityType = Driver::class;
                break;

            case 'Criança':
                // Attempt to fetch an existing kid from the database, or create a new one if none exist
                $kid = Kid::inRandomOrder()->first();
                $relatedEntityId = $kid ? $kid->id : Kid::factory()->create()->id;
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
