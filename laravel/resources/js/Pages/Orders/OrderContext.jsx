import React, { createContext, useCallback, useState } from 'react';

export const OrderContext = createContext();

export const OrderProvider = ({ children }) => {
	const [waypoints, setWaypoints] = useState([]);
	const [places, setPlaces] = useState([]);
	const [trajectory, setTrajectory] = useState([]);
	const [summary, setSummary] = useState({ distance: 0, time: 0 });

	const updateWaypoints = useCallback((newWaypoints) => {
		console.log('updating waypoints...', newWaypoints);
		setWaypoints(newWaypoints);
	}, []);

	const updateTrajectory = useCallback((newTrajectory) => {
		setTrajectory(newTrajectory);
	},[]);
	
	const updatePlaces = useCallback((newPlaces) => {
		console.log('updating places...', newPlaces)
		setPlaces(newPlaces);
	}, [])

	const updateSummary = (newSummary) => {
		setSummary(newSummary);
	};
	
console.log('waypoints', waypoints)
console.log('places', places)
	return (
		<OrderContext.Provider 
			value={{
				waypoints,
				places,
				trajectory,	
				summary,
				updateWaypoints,
				updatePlaces,
				updateTrajectory,
				updateSummary,
			}}
		>
			{children}
		</OrderContext.Provider>
	);
};
