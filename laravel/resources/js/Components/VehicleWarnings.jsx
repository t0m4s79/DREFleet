import { Link } from "@inertiajs/react";
import { Badge } from "@mui/material";

export default function VehicleWarnings({ expired, expiring, vehicle = null, userType }) {
    if (userType !== "Administrador" && userType !== "Gestor") {
        return null;
    }

    const badges = (
        <div>
            {expired > 0 && <Badge badgeContent={expired} sx={{ "& .MuiBadge-badge": { color: "white", marginTop: "2px" } }} color="error" className="mr-3">❌</Badge>}
            {expiring > 0 && (
                <Badge badgeContent={expiring} sx={{ "& .MuiBadge-badge": { backgroundColor: "#FFC700", color: "black", marginTop: "2px" } }}>
                    ⚠️
                </Badge>
            )}
        </div>
    );

    return vehicle ? (
        <Link key={vehicle} href={route('vehicles.documentsAndAccessories', vehicle)}>
            {badges}
        </Link>
    ) : badges;
}
