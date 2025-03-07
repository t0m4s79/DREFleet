import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Box } from '@mui/material';
import { parseVehicles } from '@/utils/Dashboard/vehicles';
import { parseOrders } from '@/utils/Dashboard/orders';
import { Badge } from "@mui/material";
import { driversExpirations, parseDrivers } from '@/utils/Dashboard/drivers';
import { parseTechnicians } from '@/utils/Dashboard/technicians';
import { PieChart } from '@mui/x-charts/PieChart';
import { useDrawingArea } from '@mui/x-charts/hooks';
import { styled } from '@mui/material/styles';
import { useState } from 'react';
import RefuelRequestsByMonthChart from '@/Components/Charts/RefuelRequestsByMonthChart';
import RefuelRequestsChart from '@/Components/Charts/RefuelRequestsChart';
import MaintenanceRequestsChart from '@/Components/Charts/MaintenanceRequestsChart';
import MaintenanceRequestsByMonthChart from '@/Components/Charts/MaintenanceRequestsByMonth';
import KilometersChart from '@/Components/Charts/KilometersChart';
import DriversExpirationsList from '@/Components/Dashboard/DriversExpirationsList';
import VehicleExpirationsList from '@/Components/Dashboard/VehicleExpirationsList';
import DriverModal from '@/Components/DriverModal';
import TechnicianModal from '@/Components/TechnicianModal';
import VehicleModal from '@/Components/VehicleModal';
import OrderModal from '@/Components/OrderModal';
import TopVehiclesRefuelRequests from '@/Components/Charts/TopVehiclesRefuelRequests';
import TopVehiclesMaintenanceRequests from '@/Components/Charts/TopVehiclesMaintenanceRequest';

export default function Dashboard({ auth, drivers = [], technicians = [], vehicles = [], orders = [], refuelRequests = [], maintenanceRequests = [], kilometersReports = [] }) {
    const [driverModal, setDriverModal] = useState(null);
    const [technicianModal, setTechnicianModal] = useState(null);
    const [vehiclesModal, setVehiclesModal] = useState(null);
    const [ordersModal, setOrdersModal] = useState(null);

    const userType = auth.user.user_type;

    const { inServiceDrivers, availableDrivers } = parseDrivers(drivers);
    const { inServiceTechnicians, availableTechnicians } = parseTechnicians(technicians);
    const { inServiceVehicles, availableVehicles, inMaintenanceVehicles } = parseVehicles(vehicles);
    const { ongoingOrders, approvedOrders, ordersToAprove } = parseOrders(orders);

    const handleSliceClick = (event, data, type, pieChartData) => {
        const sliceData = pieChartData[data.dataIndex];

        if (type === "driver") {
            if (sliceData.label.includes("Em Serviço")) {
                setDriverModal(inServiceDrivers);
            } else if (sliceData.label.includes("Disponíveis")) {
                setDriverModal(availableDrivers);
            }
        } else if (type === "technician") {
            if (sliceData.label.includes("Em Serviço")) {
                setTechnicianModal(inServiceTechnicians);
            } else if (sliceData.label.includes("Disponíveis")) {
                setTechnicianModal(availableTechnicians);
            }
        } else if (type === "vehicles") {
            if (sliceData.label.includes("Em Serviço")) {
                setVehiclesModal(inServiceVehicles);
            } else if (sliceData.label.includes("Disponíveis")) {
                setVehiclesModal(availableVehicles);
            } else if (sliceData.label.includes("Em Manutenção")) {
                setVehiclesModal(inMaintenanceVehicles);
            }
        } else if (type === "orders") {
            if (sliceData.label.includes("Em Curso")) {
                setOrdersModal(ongoingOrders);
            } else if (sliceData.label.includes("Agendados")) {
                setOrdersModal(approvedOrders);
            } else if (sliceData.label.includes("Por Aprovar")) {
                setOrdersModal(ordersToAprove);
            }
        }
    };

    const driversExpirationsMap = driversExpirations(drivers);

    const driversPieChartData = [
        { label: `Em Serviço (${inServiceDrivers.length})`, value: inServiceDrivers.length },
        { label: `Disponíveis (${availableDrivers.length})`, value: availableDrivers.length }
    ];

    const techniciansPieChartData = [
        { label: `Em Serviço (${inServiceTechnicians.length})`, value: inServiceTechnicians.length },
        { label: `Disponíveis (${availableTechnicians.length})`, value: availableTechnicians.length }
    ];

    const vehiclesPieChartData = [
        { label: `Em Serviço (${inServiceVehicles.length})`, value: inServiceVehicles.length },
        { label: `Disponíveis (${availableVehicles.length})`, value: availableVehicles.length },
        { label: `Em Manutenção (${inMaintenanceVehicles.length})`, value: inMaintenanceVehicles.length }
    ];

    const ordersPieChartData = [
        { label: `Em Curso (${ongoingOrders.length})`, value: ongoingOrders.length },
        { label: `Agendados (${approvedOrders.length})`, value: approvedOrders.length },
        { label: `Por Aprovar (${ordersToAprove.length})`, value: ordersToAprove.length }
    ]

    const StyledText = styled('text')(({ theme }) => ({
        fill: theme.palette.text.primary,
        textAnchor: 'middle',
        dominantBaseline: 'central',
        fontSize: 18,
    }));

    function PieCenterLabel({ children }) {
        const { width, height, left, top } = useDrawingArea();
        return (
            <StyledText x={left + width / 2} y={top + height / 2}>
                {children}
            </StyledText>
        );
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Painel de Controlo</h2>}
        >
            <Head title="Painel de Controlo" />

            {(userType === "Administrador" || userType === "Gestor") &&
                <VehicleExpirationsList vehicles={vehicles} userType={userType} />
            }

            {(userType === "Administrador" || userType === "Gestor") && (
                <DriversExpirationsList driversExpirationsMap={driversExpirationsMap} />
            )}

            <div className="pt-8">
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="text-2xl p-6 text-gray-900 font-bold">Informação Geral</div>
                    </div>
                </div>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {(userType === "Administrador" || userType === "Gestor") && driversPieChartData.some(driver => driver.value > 0) &&
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8 border-sky-600">
                            <div className="p-6 text-gray-900">
                                <Box>
                                    <PieChart
                                        margin={{ right: 80 }}
                                        series={[
                                            {
                                                data: driversPieChartData,
                                                innerRadius: 75,
                                            }
                                        ]}
                                        width={500}
                                        height={200}
                                        onItemClick={(_, data) => handleSliceClick(null, data, "driver", driversPieChartData)}
                                    >
                                        <PieCenterLabel>Condutores</PieCenterLabel>
                                    </PieChart>
                                </Box>


                                <DriverModal isOpen={driverModal !== null} onClose={() => setDriverModal(null)} driverModal={driverModal} />
                            </div>
                        </div>
                    }

                    {(userType === "Administrador" || userType === "Gestor") && techniciansPieChartData.some(technician => technician.value > 0) &&
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8 border-sky-600">
                            <div className="p-6 text-gray-900">
                                <Box>
                                    <PieChart
                                        margin={{ right: 80 }}
                                        series={[
                                            {
                                                data: techniciansPieChartData,
                                                innerRadius: 75,
                                            }
                                        ]}
                                        width={500}
                                        height={200}
                                        onItemClick={(_, data) => handleSliceClick(null, data, "technician", techniciansPieChartData)}
                                    >
                                        <PieCenterLabel>Técnicos</PieCenterLabel>
                                    </PieChart>
                                </Box>

                                <TechnicianModal isOpen={technicianModal !== null} onClose={() => setTechnicianModal(null)} technicianModal={technicianModal} />
                            </div>
                        </div>
                    }

                    {(userType === "Administrador" || userType === "Gestor" || userType === "Condutor") && vehiclesPieChartData.some(vehicle => vehicle.value > 0) &&
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8 border-sky-600">
                            <div className="p-6 text-gray-900">
                                <Box>
                                    <PieChart
                                        margin={{ right: 80 }}
                                        series={[
                                            {
                                                data: vehiclesPieChartData,
                                                innerRadius: 75,
                                            }
                                        ]}
                                        width={500}
                                        height={200}
                                        onItemClick={(_, data) => handleSliceClick(null, data, "vehicles", vehiclesPieChartData)}
                                    >
                                        <PieCenterLabel>Veículos</PieCenterLabel>
                                    </PieChart>
                                </Box>

                                <VehicleModal isOpen={vehiclesModal !== null} onClose={() => setVehiclesModal(null)} vehicleModal={vehiclesModal} userType={auth.user.user_type} />
                            </div>
                        </div>
                    }

                    {(userType === "Administrador" || userType === "Gestor" || userType === "Condutor" || userType === "Técnico") && ordersPieChartData.some(order => order.value > 0) &&
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8 border-sky-600">
                            <div className="p-6 text-gray-900">
                                <Box>
                                    <PieChart
                                        margin={{ right: 80 }}
                                        series={[
                                            {
                                                data: ordersPieChartData,
                                                innerRadius: 75,
                                            }
                                        ]}
                                        width={500}
                                        height={200}
                                        onItemClick={(_, data) => handleSliceClick(null, data, "orders", ordersPieChartData)}
                                    >
                                        <PieCenterLabel>Pedidos</PieCenterLabel>
                                    </PieChart>
                                </Box>

                                <OrderModal isOpen={ordersModal != null} onClose={() => setOrdersModal(null)} orderModal={ordersModal} auth={auth} />
                            </div>
                        </div>
                    }
                </div>

                <div className="pt-8">
                    <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="text-2xl p-6 text-gray-900 font-bold">Registos dos Veículos</div>
                        </div>

                        <div className="max-w-7xl my-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <RefuelRequestsChart refuelRequests={refuelRequests} />

                            <MaintenanceRequestsChart maintenanceRequests={maintenanceRequests} />

                            <TopVehiclesRefuelRequests refuelRequests={refuelRequests} />

                            <TopVehiclesMaintenanceRequests maintenanceRequests={maintenanceRequests} />

                            {/*
                            <RefuelRequestsByMonthChart refuelRequests={refuelRequests} />

                            <MaintenanceRequestsByMonthChart maintenanceRequests={maintenanceRequests} />

                            <KilometersChart kilometersReports={kilometersReports} />
                            */}
                        </div>
                    </div>
                </div>

            </div>
        </AuthenticatedLayout>
    );
}
