import { DataGrid, GridToolbar } from '@mui/x-data-grid'
import { ptPT } from '@mui/x-data-grid/locales'
import React, { useState } from 'react'

export default function CustomDataGrid({ rows, columns, columnVisibility, getRowClassName, editAction, deleteAction }) {

    const [columnVisibilityModel, setColumnVisibilityModel] = useState(columnVisibility)

    // Add static column with Edit and Delete buttons
    if (editAction || deleteAction) {
        columns.push({
            field: 'actions',
            headerName: 'Ações',
            hideable: false,
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
    }

    return (
        <div style={{ display: 'flex'}}>
            <DataGrid
                rows={rows}
                columns={columns}
                getRowClassName={getRowClassName}
                columnVisibilityModel={columnVisibilityModel}
                onColumnVisibilityModelChange={(newModel) =>
                    setColumnVisibilityModel(newModel)
                }
                initialState={{
                    pagination: {
                        paginationModel: { pageSize: 25,},
                    },
                }}
                pagination
                disableSelectionOnClick
                autosizeOnMount
                density='compact'
                disableDensitySelector
                slots={{ toolbar: GridToolbar }}
                slotProps={{
                    toolbar: {
                        showQuickFilter: true,
                    },
                }}
                columnBufferPx={100}
                ignoreDiacritics={true}
                hideFooterSelectedRowCount
                localeText={ptPT.components.MuiDataGrid.defaultProps.localeText}
                // Table header styling was from github Issue https://github.com/mui/mui-x/issues/898#issuecomment-1498361362
                // Currently there is no simple way to change header text warp
                sx={{
                    "& .MuiDataGrid-columnHeaderTitle": {
                        whiteSpace: "nowrap",
                        lineHeight: "normal",
                        marginTop: "12px", 
                        marginBottom: "12px", 
                        color: "#A6A6A6", 
                        fontSize: 12, 
                        textTransform: "uppercase", 
                        fontWeight:"500", 
                        fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif", 
                        letterSpacing: "0.05em"
                    },
                }}
            />
        </div>
    )
}
