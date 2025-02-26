import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Accordion, AccordionDetails, AccordionSummary, Box, Chip } from '@mui/material';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import VehicleWarnings from '@/Components/VehicleWarnings';
import { parseVehicles, vehicleExpirations } from '@/utils/Dashboard/vehicles';
import { parseOrders } from '@/utils/Dashboard/orders';
import { Badge } from "@mui/material";
import { parseDrivers } from '@/utils/Dashboard/drivers';
import { parseTechnicians } from '@/utils/Dashboard/technicians';
import { LineChart } from '@mui/x-charts';
import { PieChart } from '@mui/x-charts/PieChart';
import { useDrawingArea } from '@mui/x-charts/hooks';
import { styled } from '@mui/material/styles';
import { useState } from 'react';
import { parseRequests } from '@/utils/Dashboard/requests';

const renderOrderStatus = (status) => {
    const colors = {
        'Finalizado': 'success',
        'Aprovado': 'success',
        'Em curso': 'info',
        'Interrompido': 'warning',
        'Cancelado/Não aprovado': 'error',
        'Por aprovar': 'default',
    };

    return <Chip label={status} color={colors[status]} variant="outlined" size="medium" className='ml-2' />;
}

export default function Dashboard({ auth, drivers = [], technicians = [], vehicles = [], orders = [], refuelRequests = [], maintenanceRequests = [], permissions }) {
    const [expandedAccordion, setExpandedAccordion] = useState(null);
    const [expandedAccordionType, setExpandedAccordionType] = useState(null);

    const userType = auth.user.user_type;

    const handleSliceClick = (event, data, type, pieChartData) => {
        const sliceData = pieChartData[data.dataIndex];

        let newAccordion = null;

        switch (sliceData.label) {
            case `Em Serviço (${sliceData.value})`:
                newAccordion = "inService";
                break;
            case `Disponíveis (${sliceData.value})`:
                newAccordion = "available";
                break;
            case `Em Manutenção (${sliceData.value})`:
                newAccordion = "inMaintenance";
                break;
            case `Em Curso (${sliceData.value})`:
                newAccordion = "ongoing";
                break;
            case `Agendados (${sliceData.value})`:
                newAccordion = "approved";
                break;
            case `Por Aprovar (${sliceData.value})`:
                newAccordion = "toApprove";
                break;
            default:
                return;
        }

        if (expandedAccordion === newAccordion && expandedAccordionType === type) {
            setExpandedAccordion(null);
            setExpandedAccordionType(null);
        } else {
            setExpandedAccordion(newAccordion);
            setExpandedAccordionType(type);
        }
    };

    const { inServiceVehicles, availableVehicles, inMaintenanceVehicles } = parseVehicles(vehicles);
    const { ongoingOrders, approvedOrders, ordersToAprove } = parseOrders(orders);
    const { inServiceDrivers, availableDrivers } = parseDrivers(drivers);
    const { inServiceTechnicians, availableTechnicians } = parseTechnicians(technicians);

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

    const refuelRequestsData = parseRequests(refuelRequests);
    const maintenanceRequestsData = parseRequests(maintenanceRequests);

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
                <div className="w-full pt-10 flex flex-col items-center">
                    {vehicles.some(vehicle => {
                        const [expired, expiring] = vehicleExpirations(vehicle);
                        return expired > 0 || expiring > 0;
                    }) && (
                            <div className="w-full max-w-7xl bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-md mb-6">
                                <div className="flex items-center mb-2">
                                    <span className="text-2xl mr-3">🚨</span>
                                    <span className="text-lg font-semibold">
                                        Existem
                                        <Badge
                                            badgeContent={vehicles.filter(v => {
                                                const [expired, expiring] = vehicleExpirations(v);
                                                return expired > 0 || expiring > 0;
                                            }).length}
                                            color="error"
                                            sx={{ mx: 1.5 }}
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
            }

            <div className="pt-8">
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="text-2xl p-6 text-gray-900 font-bold">Informação Geral</div>
                    </div>
                </div>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {(userType === "Administrador" || userType === "Gestor") &&
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

                                {expandedAccordion === "inService" && expandedAccordionType === "driver" && (
                                    <Accordion expanded style={{ boxShadow: 'none' }}>
                                        <AccordionSummary
                                            expandIcon={<ExpandMoreIcon />}
                                            aria-controls="panel1-content"
                                            id="panel1-header"
                                            className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                            onClick={() =>
                                                setExpandedAccordion(expandedAccordion === "inService" ? null : "inService")
                                            }
                                        />
                                        <AccordionDetails>
                                            {inServiceDrivers.length > 0 ? (
                                                inServiceDrivers.map(driver => (
                                                    <div key={`driver-${driver.id}`}>
                                                        <a href={route('drivers.edit', driver.id)}>
                                                            #{driver.id} - {driver.name} - {driver.driver.license_number}
                                                        </a>
                                                    </div>
                                                ))
                                            ) : (
                                                <div>Nenhum condutor em serviço.</div>
                                            )}
                                        </AccordionDetails>
                                    </Accordion>
                                )}

                                {expandedAccordion === "available" && expandedAccordionType === "driver" && (
                                    <Accordion expanded style={{ boxShadow: 'none' }}>
                                        <AccordionSummary
                                            expandIcon={<ExpandMoreIcon />}
                                            aria-controls="panel2-content"
                                            id="panel2-header"
                                            className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                            onClick={() =>
                                                setExpandedAccordion(expandedAccordion === "available" ? null : "available")
                                            }
                                        />
                                        <AccordionDetails>
                                            {availableDrivers.length > 0 ? (
                                                availableDrivers.map(driver => (
                                                    <div key={`driver-${driver.id}`}>
                                                        <a href={route('drivers.edit', driver.id)}>
                                                            #{driver.id} - {driver.name} - {driver.driver.license_number}
                                                        </a>
                                                    </div>
                                                ))
                                            ) : (
                                                <div>Nenhum condutor disponível.</div>
                                            )}
                                        </AccordionDetails>
                                    </Accordion>
                                )}
                            </div>
                        </div>
                    }

                    {(userType === "Administrador" || userType === "Gestor") &&
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

                                {expandedAccordion === "inService" && expandedAccordionType === "technician" && (
                                    <Accordion expanded style={{ boxShadow: 'none' }}>
                                        <AccordionSummary
                                            expandIcon={<ExpandMoreIcon />}
                                            aria-controls="panel1-content"
                                            id="panel1-header"
                                            className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                            onClick={() =>
                                                setExpandedAccordion(expandedAccordion === "inService" ? null : "inService")
                                            }
                                        />
                                        <AccordionDetails>
                                            {inServiceTechnicians.length > 0 ? (
                                                inServiceTechnicians.map(technician => (
                                                    <div>
                                                        <a key={`technician-${technician.id}`} href={route('technicians.edit', technician)}>
                                                            #{technician.id} - {technician.name}
                                                        </a>
                                                    </div>
                                                ))
                                            ) : (
                                                <div>Nenhum técnico em serviço.</div>
                                            )}
                                        </AccordionDetails>
                                    </Accordion>
                                )}

                                {expandedAccordion === "available" && expandedAccordionType === "technician" && (
                                    <Accordion expanded style={{ boxShadow: 'none' }}>
                                        <AccordionSummary
                                            expandIcon={<ExpandMoreIcon />}
                                            aria-controls="panel2-content"
                                            id="panel2-header"
                                            className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                            onClick={() =>
                                                setExpandedAccordion(expandedAccordion === "available" ? null : "available")
                                            }
                                        />
                                        <AccordionDetails>
                                            {availableTechnicians.length > 0 ? (
                                                availableTechnicians.map(technician => (
                                                    <div>
                                                        <a key={`technician-${technician.id}`} href={route('technicians.edit', technician)}>
                                                            #{technician.id} - {technician.name}
                                                        </a>
                                                    </div>
                                                ))
                                            ) : (
                                                <div>Nenhum técnico disponível.</div>
                                            )}
                                        </AccordionDetails>
                                    </Accordion>
                                )}
                            </div>
                        </div>
                    }

                    {(userType === "Administrador" || userType === "Gestor" || userType === "Condutor") && vehicles.length > 0 &&
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

                                {expandedAccordion === "inService" && expandedAccordionType === "vehicles" && (
                                    <Accordion expanded style={{ boxShadow: 'none' }}>
                                        <AccordionSummary
                                            expandIcon={<ExpandMoreIcon />}
                                            aria-controls="panel1-content"
                                            id="panel1-header"
                                            className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                            onClick={() =>
                                                setExpandedAccordion(expandedAccordion === "inService" ? null : "inService")
                                            }
                                        />
                                        <AccordionDetails>
                                            {vehicles.filter(vehicle => vehicle.status === 'Em Serviço').length > 0 ? (
                                                vehicles.filter(vehicle => vehicle.status === 'Em Serviço').map(vehicle => {
                                                    const [expired, expiring] = vehicleExpirations(vehicle);

                                                    return (
                                                        <div key={`vehicle-${vehicle.id}`} className="flex items-center gap-3 pb-3">
                                                            <a href={route('vehicles.edit', vehicle)}>
                                                                #{vehicle.id} - {vehicle.make} {vehicle.model} - {vehicle.license_plate}
                                                            </a>

                                                            <VehicleWarnings expired={expired} expiring={expiring} vehicle={vehicle.id} userType={userType} />
                                                        </div>
                                                    );
                                                })
                                            ) : (
                                                <div>Nenhum veículo em serviço.</div>
                                            )}
                                        </AccordionDetails>
                                    </Accordion>
                                )}

                                {expandedAccordion === "available" && expandedAccordionType === "vehicles" && (
                                    <Accordion expanded style={{ boxShadow: 'none' }}>
                                        <AccordionSummary
                                            expandIcon={<ExpandMoreIcon />}
                                            aria-controls="panel2-content"
                                            id="panel2-header"
                                            className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                            onClick={() =>
                                                setExpandedAccordion(expandedAccordion === "available" ? null : "available")
                                            }
                                        />
                                        <AccordionDetails>
                                            {vehicles.filter(vehicle => vehicle.status === 'Disponível').length > 0 ? (
                                                vehicles.filter(vehicle => vehicle.status === 'Disponível').map(vehicle => {
                                                    const [expired, expiring] = vehicleExpirations(vehicle);

                                                    return (
                                                        <div key={`vehicle-${vehicle.id}`} className="flex items-center gap-3 pb-3">
                                                            <a href={route('vehicles.edit', vehicle)}>
                                                                #{vehicle.id} - {vehicle.make} {vehicle.model} - {vehicle.license_plate}
                                                            </a>

                                                            <VehicleWarnings expired={expired} expiring={expiring} vehicle={vehicle.id} userType={userType} />
                                                        </div>
                                                    )
                                                })
                                            ) : (
                                                <div>Nenhum veículo disponível.</div>
                                            )}
                                        </AccordionDetails>
                                    </Accordion>
                                )}

                                {expandedAccordion === "inMaintenance" && expandedAccordionType === "vehicles" && (
                                    <Accordion expanded style={{ boxShadow: 'none' }}>
                                        <AccordionSummary
                                            expandIcon={<ExpandMoreIcon />}
                                            aria-controls="panel2-content"
                                            id="panel2-header"
                                            className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                            onClick={() =>
                                                setExpandedAccordion(expandedAccordion === "inMaintenance" ? null : "inMaintenance")
                                            }
                                        />
                                        <AccordionDetails>
                                            {vehicles.filter(vehicle => vehicle.status === 'Em manutenção').length > 0 ? (
                                                vehicles.filter(vehicle => vehicle.status === 'Em manutenção').map(vehicle => {
                                                    const [expired, expiring] = vehicleExpirations(vehicle);

                                                    return (
                                                        <div key={`vehicle-${vehicle.id}`} className="flex items-center gap-3 pb-3">
                                                            <a href={route('vehicles.edit', vehicle)}>
                                                                #{vehicle.id} - {vehicle.make} {vehicle.model} - {vehicle.license_plate}
                                                            </a>

                                                            <VehicleWarnings expired={expired} expiring={expiring} vehicle={vehicle.id} userType={userType} />
                                                        </div>
                                                    )
                                                })
                                            ) : (
                                                <div>Nenhum veículo em manutenção.</div>
                                            )}
                                        </AccordionDetails>
                                    </Accordion>
                                )}
                            </div>
                        </div>
                    }

                    {(userType === "Administrador" || userType === "Gestor" || userType === "Condutor" || userType === "Técnico") && orders.length > 0 &&
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

                                {expandedAccordion === "ongoing" && expandedAccordionType === "orders" && (
                                    <Accordion expanded style={{ boxShadow: 'none' }}>
                                        <AccordionSummary
                                            expandIcon={<ExpandMoreIcon />}
                                            aria-controls="panel1-content"
                                            id="panel1-header"
                                            className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                            onClick={() =>
                                                setExpandedAccordion(expandedAccordion === "ongoing" ? null : "ongoing")
                                            }
                                        />
                                        <AccordionDetails>
                                            {ongoingOrders.length > 0 ? (
                                                ongoingOrders.map(order => {
                                                    const orderLink = (permissions.isTechnician || permissions.isDriver)
                                                        ? order.status === "Em curso"
                                                            ? route('orders.showStopOrder', order)
                                                            : route('orders.showStartOrder', order)
                                                        : route('orders.edit', order);

                                                    return (
                                                        <div key={`order-${order.id}`}>
                                                            <a href={orderLink}>
                                                                #{order.id} - {order.order_type} - {order.expected_begin_date} a {order.expected_end_date}
                                                                <span>{renderOrderStatus(order.status)}</span>
                                                            </a>
                                                        </div>
                                                    );
                                                })
                                            ) : (
                                                <div>Nenhum pedido em curso.</div>
                                            )}
                                        </AccordionDetails>
                                    </Accordion>
                                )}

                                {expandedAccordion === "approved" && expandedAccordionType === "orders" && (
                                    <Accordion expanded style={{ boxShadow: 'none' }}>
                                        <AccordionSummary
                                            expandIcon={<ExpandMoreIcon />}
                                            aria-controls="panel2-content"
                                            id="panel2-header"
                                            className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                            onClick={() =>
                                                setExpandedAccordion(expandedAccordion === "approved" ? null : "approved")
                                            }
                                        />
                                        <AccordionDetails>
                                            {approvedOrders.length > 0 ? (
                                                approvedOrders.map(order => {
                                                    const orderLink = (permissions.isTechnician || permissions.isDriver) ? route('orders.showStartOrder', order) : route('orders.edit', order);

                                                    return (
                                                        <div key={`order-${order.id}`}>
                                                            <a href={orderLink}>
                                                                #{order.id} - {order.order_type} - {order.expected_begin_date} a {order.expected_end_date}
                                                            </a>
                                                        </div>
                                                    )
                                                })
                                            ) : (
                                                <div>Nenhum pedido agendado.</div>
                                            )}
                                        </AccordionDetails>
                                    </Accordion>
                                )}

                                {expandedAccordion === "toApprove" && expandedAccordionType === "orders" && (
                                    <Accordion expanded style={{ boxShadow: 'none' }}>
                                        <AccordionSummary
                                            expandIcon={<ExpandMoreIcon />}
                                            aria-controls="panel3-content"
                                            id="panel3-header"
                                            className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                            onClick={() =>
                                                setExpandedAccordion(expandedAccordion === "inMaintenance" ? null : "inMaintenance")
                                            }
                                        />
                                        <AccordionDetails>
                                            {ordersToAprove.length > 0 ? (
                                                ordersToAprove.map(order => (
                                                    <div>
                                                        {(permissions.isTechnician || permissions.isDriver) ? (
                                                            <a href={route('orders.showStartOrder', order)}>
                                                                #{order.id} - {order.order_type} - {order.expected_begin_date} a {order.expected_end_date}
                                                            </a>
                                                        ) : (
                                                            <a key={`order-${order.id}`} href={route('orders.edit', order)}>
                                                                #{order.id} - {order.order_type} - {order.expected_begin_date} a {order.expected_end_date}
                                                            </a>
                                                        )}
                                                    </div>
                                                ))
                                            ) : (
                                                <div>Nenhum pedido por aprovar.</div>
                                            )}
                                        </AccordionDetails>
                                    </Accordion>
                                )}
                            </div>
                        </div>
                    }
                </div>

                <div className="pt-8">
                    <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="text-2xl p-6 text-gray-900 font-bold">Relatórios</div>
                        </div>

                        <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-4">

                            <Box>
                                <LineChart
                                    xAxis={[{ scaleType: 'point', data: refuelRequestsData.map(d => d.month) }]}
                                    series={[{ data: refuelRequestsData.map(d => d.requests), label: "Registos de Abastecimento" }]}
                                    width={600}
                                    height={300}
                                />
                            </Box>


                            <Box>
                                <LineChart
                                    xAxis={[{ scaleType: 'point', data: maintenanceRequestsData.map(d => d.month) }]}
                                    series={[{ data: maintenanceRequestsData.map(d => d.requests), label: "Registos de Manutenção" }]}
                                    width={600}
                                    height={300}
                                />
                            </Box>
                        </div>
                    </div>
                </div>

            </div>
        </AuthenticatedLayout>
    );
}
