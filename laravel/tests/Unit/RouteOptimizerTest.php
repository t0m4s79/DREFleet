<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\RouteOptimizer;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RouteOptimizerTest extends TestCase
{
    protected $distanceMatrix;
    protected $placeIds;

    protected function setUp(): void
    {
        parent::setUp();

        // Example distance matrix and corresponding place IDs
        $this->distanceMatrix = [
            //    101   102   103
            [   0,   10,   15],  // Place 101
            [  10,    0,   20],  // Place 102
            [  15,   20,    0]   // Place 103
        ];
        $this->placeIds = [101, 102, 103]; // Example place IDs
    }

    public function testNearestNeighbor()
    {
        $optimizer = new RouteOptimizer($this->distanceMatrix, $this->placeIds);
        $result = $optimizer->nearestNeighbor();

        // Expected path should be [101, 102, 103] (A -> B -> C)
        $expectedPath = [101, 102, 103];
        $expectedDistance = 30; // 10 (A to B) + 20 (B to C)

        $this->assertEquals($expectedPath, $result['path']);
        $this->assertEquals($expectedDistance, $result['totalDistance']);
    }

    public function testPathWithDifferentStartEnd()
    {
        $distanceMatrix = [
            //    101   102   103   104
            [   0,   10,   15,   25],  // Place 101
            [  10,    0,   20,   30],  // Place 102
            [  15,   20,    0,   12],  // Place 103
            [  25,   30,   12,    0]   // Place 104
        ];
        $placeIds = [101, 102, 103, 104]; // Example place IDs

        $optimizer = new RouteOptimizer($distanceMatrix, $placeIds);
        $result = $optimizer->nearestNeighbor();

        // Updated expected path and total distance
        $expectedPath = [101, 102, 103, 104]; 
        $expectedDistance = 42; // 10 (101 to 102) + 20 (102 to 103) + 12 (103 to 104)

        $this->assertEquals($expectedPath, $result['path']);
        $this->assertEquals($expectedDistance, $result['totalDistance']);
    }
}
