import { Head, Link } from '@inertiajs/react';
import LeafletMap from '@/Components/LeafletMap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import 'leaflet/dist/leaflet.css';

export default function RoutesExample({auth}) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Rotas</h2>}
        >

            <Head title="Rotas" />
        
            <div className="App">
                <LeafletMap />
            </div>
        </AuthenticatedLayout>
    );
}