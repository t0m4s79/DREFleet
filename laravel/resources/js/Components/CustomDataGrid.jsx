import { DataGrid, GridToolbar } from '@mui/x-data-grid'
import { ptPT } from '@mui/x-data-grid/locales'
import React, { useState } from 'react'
import { Dialog, DialogActions, DialogContent, DialogTitle, Tooltip, Button } from "@mui/material";
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import ContentCopyIcon from '@mui/icons-material/ContentCopy';

export default function CustomDataGrid({ rows, columns, columnVisibility, getRowClassName, editAction, deleteAction, duplicateAction, user }) {

    const [columnVisibilityModel, setColumnVisibilityModel] = useState(columnVisibility)
    const [modalOpen, setModalOpen] = useState(false);
    const [selectedId, setSelectedId] = useState(null);
    const [actionType, setActionType] = useState(null);

    const openModal = (id, action) => {
        setSelectedId(id);
        setActionType(action);
        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setSelectedId(null);
        setActionType(null);
    };

    // Add static column with Edit and Delete buttons
    if ((editAction || deleteAction || duplicateAction) && (user.user_type === "Administrador" || user.user_type === "Gestor")) {
        columns.push({
            field: 'actions',
            headerName: 'Ações',
            hideable: false,
            renderCell: (params) => (
                <div className="flex space-x-2">
                    {editAction && (
                        <Tooltip title="Editar">
                            <a href={route(editAction, params.row.id)}>
                                <EditIcon size={18} className="text-blue-500 hover:text-blue-700" />
                            </a>
                        </Tooltip>
                    )}
                    {duplicateAction && (
                        <Tooltip title="Duplicar">
                            <button onClick={() => openModal(params.row.id, "duplicate")}>
                                <ContentCopyIcon size={18} className="text-green-500 hover:text-green-700" />
                            </button>
                        </Tooltip>
                    )}
                    {deleteAction && (
                        <Tooltip title="Eliminar">
                            <button onClick={() => openModal(params.row.id, "delete")}>
                                <DeleteIcon size={18} className="text-red-500 hover:text-red-700" />
                            </button>
                        </Tooltip>
                    )}
                </div>
            ),
            sortable: false, // Disable sorting for actions column
            minWidth: 120, // Adjust as needed
        });
    }


    const confirmAction = async () => {
        if (actionType === "duplicate") {
            try {
                const response = await axios.post(route(duplicateAction, selectedId));
                window.location.href = response.request.responseURL;
            } catch (error) {
                console.error("Erro ao duplicar o registo:", error);
            }
        } else if (actionType === "delete") {
            const form = document.createElement('form');
            form.action = route(deleteAction, selectedId);
            form.method = 'POST';

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

        closeModal();
    };

    return (
        <>
            <div style={{ display: 'flex' }}>
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
                            paginationModel: { pageSize: 25, },
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

            <Dialog open={modalOpen} onClose={closeModal}>
                <DialogTitle>Confirmação</DialogTitle>
                <DialogContent>
                    <p>Tem a certeza que pretende {actionType === "delete" ? "eliminar" : "duplicar"} o elemento com id <b>{selectedId}</b>?</p>
                </DialogContent>
                <DialogActions>
                    <Button onClick={closeModal} color="error">
                        Cancelar
                    </Button>
                    <Button onClick={confirmAction} color="primary" variant="contained">
                        Confirmar
                    </Button>
                </DialogActions>
            </Dialog>
        </>
    )
}
