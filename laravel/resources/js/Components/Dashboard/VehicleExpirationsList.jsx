import { vehicleExpirations } from "@/utils/Dashboard/vehicles";
import VehicleWarnings from "../VehicleWarnings";
import { Accordion, AccordionDetails, AccordionSummary, Badge } from "@mui/material";
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';


export default function VehicleExpirationsList({ vehicles, userType }) {
    return (
        <div className="w-full pt-10 flex flex-col items-center">
            {vehicles.some(vehicle => {
                const [expired, expiring] = vehicleExpirations(vehicle);
                return expired > 0 || expiring > 0;
            }) && (
                    <div className="w-full max-w-7xl bg-red-100 border-l-8 border-red-500 text-red-700 p-4 rounded-lg shadow-md mb-6">
                        <div className="flex items-center mb-2">
                            <span className="text-2xl mr-2">🚨</span>
                            <span className="text-lg font-semibold">
                                Existem
                                <Badge
                                    badgeContent={vehicles.filter(v => {
                                        const [expired, expiring] = vehicleExpirations(v);
                                        return expired > 0 || expiring > 0;
                                    }).length}
                                    color="error"
                                    sx={{ mx: 1.8, mb:0.5 }}
                                />
                                veículos com problemas de documentos/acessórios.
                            </span>
                        </div>

                        <Accordion className="w-full shadow-md rounded-lg overflow-hidden">
                            <AccordionSummary
                                expandIcon={<ExpandMoreIcon />}
                                aria-controls="panel-warning-content"
                                id="panel-warning-header"
                                className="hover:transition hover:text-gray-500 aria-expanded:text-red-600 aria-expanded:font-bold"
                            >
                                <span className="flex items-center text-red-700">
                                    <span className="text-lg font-semibold">📋 Ver detalhes dos veículos afetados</span>
                                </span>
                            </AccordionSummary>

                            <AccordionDetails className="bg-white p-4 rounded-b-lg">
                                {vehicles.filter(vehicle => {
                                    const [expired, expiring] = vehicleExpirations(vehicle);
                                    return expired > 0 || expiring > 0;
                                }).map(vehicle => {
                                    const [expired, expiring] = vehicleExpirations(vehicle);

                                    return (
                                        <div key={`vehicle-${vehicle.id}`} className="flex items-center gap-3 pb-3 border-b border-gray-200 last:border-none py-2">
                                            <a href={route('vehicles.documentsAndAccessories', vehicle)} className="font-semibold">
                                                #{vehicle.id} - {vehicle.make} {vehicle.model} - {vehicle.license_plate}
                                            </a>

                                            <VehicleWarnings expired={expired} expiring={expiring} vehicle={vehicle.id} userType={userType} />
                                        </div>
                                    );
                                })}
                            </AccordionDetails>
                        </Accordion>
                    </div>
                )}
        </div>
    )
}