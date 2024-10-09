
export const calculateTravelMetrics = (instructions, waypoints) => {

    // Calculate distance and time between waypoints using instructions
    const waypointData = [];
    let accumulatedDistance = 0;
    let accumulatedTime = 0;
    let currentWaypointIndex = 1;

    // Push data of the first waypoint, distance and time are zero
    waypointData.push({
        //place: waypoints[0],
        distance: 0,
        time: 0
    })

    instructions.forEach(instruction => {
        accumulatedDistance += instruction.distance;
        accumulatedTime += instruction.time;
        //console.log(instruction)
        // Check if we've reached the next waypoint
        if (instruction.type == "WaypointReached" || instruction.type == "DestinationReached") {

            waypointData.push({
                //place: waypoints[currentWaypointIndex],
                distance: accumulatedDistance,
                time: accumulatedTime
            });

            // Reset accumulators for the next segment
            accumulatedDistance = 0;
            accumulatedTime = 0;

            currentWaypointIndex += 1;
        }
    })
    return waypointData;
}