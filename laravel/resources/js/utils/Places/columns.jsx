import { Link } from "@inertiajs/react";
import { Button } from "@mui/material";

const columns = [
    {
        field: 'id',
        headerName: 'ID',
        flex: 1,
        maxWidth: 100,
        hideable: false
    },
    {
        field: 'address',
        headerName: 'Morada',
        flex: 1,
    },
    {
        field: 'known_as',
        headerName: 'Conhecido como',
        flex: 1,
        maxWidth: 200,
    },
    {
        field: 'place_type',
        headerName: 'Tipo',
        flex: 1,
        maxWidth: 120,
    },
    {
        field: 'coordinates',
        headerName: 'Coordenadas',
        flex: 1,
    },
    {
        field: 'kids_count',
        headerName: 'Número de crianças',
        flex: 1,
        maxWidth: 150,
        align: 'center',
    },
    {
        field: 'kids_ids',
        headerName: 'Crianças',
        flex: 1,
        sortComparator: (a, b) => {
            const countA = a ? a.split(', ').length : 0;
            const countB = b ? b.split(', ').length : 0;
            return countA - countB;
        },
        renderCell: (params) => {
            const ids = params.value;

            if (ids.length > 0) {
                return (
                    <div>
                        {ids.split(', ').map((id) => (
                            <Link key={id} href={route('kids.showEdit', { id })}>
                                <Button
                                    variant="outlined"
                                    sx={{
                                        maxWidth: '30px',
                                        maxHeight: '30px',
                                        minWidth: '30px',
                                        minHeight: '30px',
                                        margin: '0px 4px'
                                    }}
                                >
                                    {id}
                                </Button>
                            </Link>
                        ))}
                    </div>
                )
            } else {
                return null;
            }
        }
    },
]

const driverColumns = ['id', 'address', 'known_as', 'place_type', 'coordinates']

export const parsePlacesColumns = (user) => {
    const filteredColumns = columns.filter((column) => {
        if (user.user_type === "Condutor") {
            return driverColumns.includes(column.field);
        }
        return true;
    });

    if (user.user_type === "Condutor") {
        return filteredColumns.map((column) => ({
            ...column,
            flex: 1
        }));
    }

    return filteredColumns;
};