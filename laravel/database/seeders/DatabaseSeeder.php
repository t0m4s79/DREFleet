<?php

namespace Database\Seeders;

use App\Models\Kid;
use App\Models\User;
use App\Models\Order;
use App\Models\Place;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use Database\Factories\ManagerFactory;
use Database\Factories\TechnicianFactory;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(6)->create();
        TechnicianFactory::new()->count(4)->create();
        ManagerFactory::new()->count(2)->create();
        Driver::factory(4)->create();
        Vehicle::factory(6)->create();
        Kid::factory(10)->create();
        Place::factory(25)->create();
        Order::factory(6)->create();

        $kids = Kid::all();
        $technicians = User::where('user_type','Técnico');
        $places = Place::where('place_type', 'Residência')->get(); // Fetch the collection of places

        // Seed kid-place pivot table
        $kids->each(function ($kid) use ($places) {
            // Randomly select 1-3 places from the fetched collection
            $kid->places()->sync(
                $places->random(rand(1, 3))->pluck('id')->toArray()
            );
        });

        // Seed kid-user pivot table
        $technicians->each(function ($technician) use ($kids) {
            $kidIds = $kids->random(rand(1, 3))->pluck('id')->toArray(); // Randomly select 1-3 kids

            // Attach each kid with a random priority (1 or 2)
            foreach ($kidIds as $kidId) {
                // Check if the relationship already exists before attaching
                if (!$technician->kids()->where('kid_id', $kidId)->exists()) {
                    $technician->kids()->attach($kidId, ['priority' => Arr::random([1, 2])]);
                }
            }
        });
    }
}
