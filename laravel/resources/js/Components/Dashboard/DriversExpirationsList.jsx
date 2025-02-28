import { Badge } from "@mui/material";
import { Accordion, AccordionDetails, AccordionSummary } from '@mui/material';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import { Link } from "@inertiajs/react";

export default function DriversExpirationsList({ driversExpirationsMap }) {

    return (
        <div className="w-full pt-4 flex flex-col items-center">
            {driversExpirationsMap.some(driver => driver.expired.length > 0 || driver.expiring.length > 0) && (
                <div className="w-full max-w-7xl bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-md mb-6">
                    <div className="flex items-center mb-2">
                        <span className="text-2xl mr-3">🚨</span>
                        <span className="text-lg font-semibold">
                            Existem
                            <Badge
                                badgeContent={driversExpirationsMap.filter(driver => driver.expired.length > 0 || driver.expiring.length > 0).length}
                                color="error"
                                sx={{ mx: 1.5 }}
                            />
                            condutores com documentos expirados ou prestes a expirar.
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
                                <span className="text-lg font-semibold">📋 Ver detalhes dos condutores afetados</span>
                            </span>
                        </AccordionSummary>

                        <AccordionDetails className="bg-white p-4 rounded-b-lg">
                            {driversExpirationsMap
                                .filter(driver => driver.expired.length > 0 || driver.expiring.length > 0)
                                .map(driver => (
                                    <div className="flex items-center gap-3 pb-3 border-b border-gray-200 last:border-none py-2">
                                        <Link href={route('drivers.index')} className="font-semibold">
                                            #{driver.driver.user_id} - {driver.driver.name} - {driver.driver.license_number}
                                        </Link>

                                        <div>
                                            {driver.expired.length > 0 && <Badge badgeContent={driver.expired.length} sx={{ "& .MuiBadge-badge": { color: "white", marginTop: "2px" } }} color="error" className="mr-3">❌</Badge>}
                                            {driver.expiring.length > 0 && (
                                                <Badge badgeContent={driver.expiring.length} sx={{ "& .MuiBadge-badge": { backgroundColor: "#FFC700", color: "black", marginTop: "2px" } }}>
                                                    ⚠️
                                                </Badge>
                                            )}
                                        </div>
                                    </div>
                                ))}
                        </AccordionDetails>
                    </Accordion>
                </div>
            )}
        </div>
    )
}