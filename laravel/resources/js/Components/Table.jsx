import React from 'react';
import { DataGrid, } from '@mui/x-data-grid';
import { Button } from '@mui/material';
import { ptPT } from '@mui/x-data-grid/locales';
import MapModal from './MapModal';
import ErrorIcon from '@mui/icons-material/Error';
import { parse, isBefore } from 'date-fns';
import { pt } from 'date-fns/locale';

// Custom table using Material UI's DataGrid
const Table = ({ data, columnsLabel = {}, editAction, deleteAction, dataId }) => {

    // Define DataGrid columns based on columnsLabel
    // columns can be different depending on the information provided and thus need to render different elements
    const columns = Object.keys(columnsLabel).map(key => ({
        field: key,
        headerName: columnsLabel[key],
        editable: false,
        flex: 1,
        renderCell: (params) => {
            //console.log(params)              Use this console log for debugging purposes         
            // Display kids_ids as buttons that direct to the respective kid's page
            // Shown in Places "table"
            if (key === 'kids_ids') {
                return (
                    <div>
                        {params.value.map((kid) => (
                            <Button
                                key={kid.id}
                                variant="outlined"
                                href={route('kids.showEdit', kid)}
                                sx={{
                                    maxWidth: '30px',
                                    maxHeight: '30px',
                                    minWidth: '30px',
                                    minHeight: '30px',
                                    margin: '0px 4px'
                                }}
                            >
                                {kid.id}
                            </Button>
                        ))}
                    </div>
                );
            }
            // Display place_ids as buttons that direct to the respective place's page
            // Shown in Kids "table"
            if (key === 'place_ids') {
                return (
                    <div>
                        {params.value.map((kid) => (
                            <Button
                                key={kid.id}
                                variant="outlined"
                                href={route('places.showEdit', kid)}
                                sx={{
                                    maxWidth: '30px',
                                    maxHeight: '30px',
                                    minWidth: '30px',
                                    minHeight: '30px',
                                    margin: '0px 4px'
                                }}
                            >
                                {kid.id}
                            </Button>
                        ))}
                    </div>
                );
            }
            // Display kidsList with buttons, each button redirecting to the respective kid's page
            // Shown in Technician "table"
            if (key === 'kidsList1' || key === 'kidsList2') {
                return (
                    <div>
                        {params.value.map((kid) => (
                            <Button
                                key={kid.id}
                                variant="outlined"
                                href={route('kids.showEdit', kid)}
                                sx={{
                                    maxWidth: '30px',
                                    maxHeight: '30px',
                                    minWidth: '30px',
                                    minHeight: '30px',
                                    margin: '0px 4px'
                                }}
                            >
                                {kid.id}
                            </Button>
                        ))}
                    </div>
                );
            }
            // Display trajectory using a Modal
            // Shown in Orders 'table'
            if(key == 'trajectory'){
                return (
                    <MapModal trajectory={params.value}/>
                )
            }
            if(key == 'orderArea'){
                return (
                    <MapModal route={params.value}/>
                )
            }
            //Display vehicle id as a Button to redirect to the respective vehicle
            //Shown in vehicle Accessories and Vehicle Documents 'table'
            if(key == 'vehicle_id'){
                return (
                    <div>
                        <Button
                            key={params.value}
                            variant="outlined"
                            href={route('vehicles.showEdit', params.value)}
                            sx={{
                                maxWidth: '30px',
                                maxHeight: '30px',
                                minWidth: '30px',
                                minHeight: '30px',
                                margin: '0px 4px'
                            }}
                        >
                            {params.value}
                        </Button>
                    </div>
                )
            }
            if(key == 'driver_id'){
                return (
                    <div>
                        <Button
                            key={params.value}
                            variant="outlined"
                            href={route('drivers.showEdit', params.value)}
                            sx={{
                                maxWidth: '30px',
                                maxHeight: '30px',
                                minWidth: '30px',
                                minHeight: '30px',
                                margin: '0px 4px'
                            }}
                        >
                            {params.value}
                        </Button>
                    </div>
                )
            }
            if(key == 'technician_id'){
                return (
                    <div>
                        <Button
                            key={params.value}
                            variant="outlined"
                            href={route('technicians.showEdit', params.value)}
                            sx={{
                                maxWidth: '30px',
                                maxHeight: '30px',
                                minWidth: '30px',
                                minHeight: '30px',
                                margin: '0px 4px'
                            }}
                        >
                            {params.value}
                        </Button>
                    </div>
                )
            }
            if(key == 'approved_by'){
                if(params.value==null) return params.value
                return (
                    <div>
                        <Button
                            key={params.value}
                            variant="outlined"
                            href={route('managers.showEdit', params.value)}
                            sx={{
                                maxWidth: '30px',
                                maxHeight: '30px',
                                minWidth: '30px',
                                minHeight: '30px',
                                margin: '0px 4px'
                            }}
                        >
                            {params.value}
                        </Button>
                    </div>
                )
            }
            if(key == 'route'){
                if(params.value != '-'){
                    return (
                        <div>
                            <Button
                                key={params.value}
                                variant="outlined"
                                href={route('orderRoutes.showEdit', params.value)}
                                sx={{
                                    maxWidth: '30px',
                                    maxHeight: '30px',
                                    minWidth: '30px',
                                    minHeight: '30px',
                                    margin: '0px 4px'
                                }}
                            >
                                {params.value}
                            </Button>
                        </div>
                    )
                } else{
                    return (
                        <div></div>
                    )
                }
            }
            if (key === 'expiration_date' || key === 'license_expiration_date') {
                const parsedDate = parse(params.value, 'dd-MM-yyyy', new Date(), { locale: pt });
                const now = new Date();
                
                if (isBefore(parsedDate, now)) {
                    return (
                        <div style={{ color: 'red' }}>
                            <ErrorIcon style={{ marginRight: '4px', color: 'red', fontWeight: 'bolder' }} />
                            {params.value}
                        </div>
                    );
                }
                return params.value
            }
            if(key === 'all_approved_orders'){
                return (
                    <div>
                        <Button
                            key={params.value}
                            variant="outlined"
                            href={route('managers.approved', params.value)}
                            sx={{
                                maxHeight: '30px',
                                minHeight: '30px',
                                margin: '0px 4px'
                            }}
                        >
                            Consultar
                        </Button>
                    </div>
                )
            }
            if(key === 'vehicle_accesories_docs'){
                return (
                    <div>
                        <Button
                            key={params.value}
                            variant="outlined"
                            href={route('vehicles.documentsAndAccessories', params.value)}
                            sx={{
                                maxHeight: '30px',
                                minHeight: '30px',
                                margin: '0px 4px'
                            }}
                        >
                            Consultar
                        </Button>
                    </div>
                )
            }
            if(key === 'vehicle_kilometrage_reports'){
                return (
                    <div>
                        <Button
                            key={params.value}
                            variant="outlined"
                            href={route('vehicles.kilometrageReports', params.value)}
                            sx={{
                                maxHeight: '30px',
                                minHeight: '30px',
                                margin: '0px 4px'
                            }}
                        >
                            Consultar
                        </Button>
                    </div>
                )
            }
            if(key === 'kid_contacts'){
                return (
                    <div>
                        <Button
                            key={params.value}
                            variant="outlined"
                            href={route('kids.contacts', params.value)}
                            sx={{
                                maxHeight: '30px',
                                minHeight: '30px',
                                margin: '0px 4px'
                            }}
                        >
                            Consultar
                        </Button>
                    </div>
                )
            }

            return params.value;
        }
    }));
    // Add static column with Edit and Delete buttons
    columns.push({
        field: 'actions',
        headerName: 'Ações',
        renderCell: (params) => (
            <div>
                {editAction && 
                    <a
                        href={route(editAction, params.row.id)}
                        className="font-medium text-blue-600 dark:text-blue-500 hover:underline"
                    >
                        Editar
                    </a>
                }
                {deleteAction && 
                    <button
                        onClick={() => handleDelete(params.row.id)}
                        className="ml-4 font-medium text-red-600 dark:text-red-500 hover:underline"
                    >
                        Eliminar
                    </button>
                }
            </div>
        ),
        sortable: false, // Disable sorting for actions column
        minWidth: 150, // Adjust as needed
    });

    const rows = data.map((elem) => ({
        id: elem[dataId], // DataGrid requires an 'id' field
        ...elem
    }));

    // Method to handle delete
    const handleDelete = async (id) => {

        if (window.confirm('Tem a certeza que pretende eliminar o elemento com id ' + id + '?')) {

            const form = document.createElement('form');
            form.action = route(deleteAction, id);
            form.method = 'POST';
    
            // Add hidden inputs for the method and CSRF token
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
    
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
            form.appendChild(methodInput);
            form.appendChild(csrfInput);
    
            document.body.appendChild(form);
            form.submit();
        }
    };

    return (
        <div>
            <DataGrid
                rows={rows}
                columns={columns}
                initialState={{
                    pagination: {
                        paginationModel: { pageSize: 25,},
                    },
                }}
                pagination
                disableSelectionOnClick
                autosizeOnMount
                autoHeight
                density='compact'
                //loading                           //loading can be used when fetching data
                hideFooterSelectedRowCount
                localeText={ptPT.components.MuiDataGrid.defaultProps.localeText}
                sx={{ fontSize: 14 }}
            />
        </div>
    );
};

export default Table;
