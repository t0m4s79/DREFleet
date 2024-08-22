<?php

namespace Database\Seeders;

use App\Models\Kid;
use App\Models\User;
use App\Models\Place;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(6)->create();
        Driver::factory(4)->create();
        Vehicle::factory(6)->create();
        Kid::factory(10)->create();
        Place::factory(15)->create();
    }
}
