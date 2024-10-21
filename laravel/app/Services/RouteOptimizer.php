<?php

namespace App\Services;

//TODO: INSTEAD OF FRONT-END GIVING DISTANCES, JUST GIVE COORDINATES, AND USE A LOCAL OSRM SERVER TO CALCULATE DISTANCE PAIRS
class RouteOptimizer
{
    protected $distanceMatrix;
    protected $placeIds;    // Store place IDs
    protected $numPoints;

    public function __construct(array $distanceMatrix, array $placeIds)
    {
        $this->distanceMatrix = $distanceMatrix;
        $this->placeIds = $placeIds;
        $this->numPoints = count($distanceMatrix);  // Square matrix
    }

    // Nearest Neighbor Algorithm (O(n²))
    public function nearestNeighbor()
    {
        $startIndex = 0;                                        // Start with the first place
        $endIndex = $this->numPoints - 1;                       // Always end at the last place
        $visited = array_fill(0, $this->numPoints, false);
        $path = [$this->placeIds[$startIndex]];                 // Use place IDs in the path
        $visited[$startIndex] = true;

        $currentIndex = $startIndex;
        $totalDistance = 0;

        // Visit all points except the last (C)
        for ($i = 1; $i < $this->numPoints - 1; $i++) {
            $nearestIndex = -1;
            $shortestDistance = PHP_INT_MAX;

            // Find the nearest unvisited point
            for ($j = 1; $j < $this->numPoints - 1; $j++) {
                if (!$visited[$j] && $this->distanceMatrix[$currentIndex][$j] < $shortestDistance) {
                    $shortestDistance = $this->distanceMatrix[$currentIndex][$j];
                    $nearestIndex = $j;
                }
            }

            // Move to the nearest point
            if ($nearestIndex != -1) {
                $path[] = $this->placeIds[$nearestIndex];
                $visited[$nearestIndex] = true;
                $totalDistance += $shortestDistance;
                $currentIndex = $nearestIndex;
            }
        }

        // Finally, add the last place
        $path[] = $this->placeIds[$endIndex];
        $totalDistance += $this->distanceMatrix[$currentIndex][$endIndex];

        return ['path' => $path, 'totalDistance' => $totalDistance];
    }
}