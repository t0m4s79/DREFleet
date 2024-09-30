<?php

namespace Database\Factories;

use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use Illuminate\Database\Eloquent\Factories\Factory;
use MatanYadaev\EloquentSpatial\Objects\LineString;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderRoute>
 */
class OrderRouteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        
        return [
            'name' => fake()->company(),
            'area' => $this->generateRandomPolygon(),
            'area_color' => fake()->hexColor(),
        ];
    }

    private function generateRandomPolygon(): Polygon
    {
        $points = [];

        $numPoints = rand(3, 10);

        for ($i = 0; $i < $numPoints; $i++) {
            $points[] = new Point(fake()->latitude(), fake()->longitude());
        }
        
        // Ensure the polygon is closed by adding the first point at the end
        $points[] = $points[0];

        return new Polygon([new LineString($points)]);
    }
}
