import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import React, { useState, useEffect } from 'react';
import LoadingAnimation from './components/LoadingAnimation'; // Import your spinner component
import { NotificationsProvider } from './Pages/Notifications/NotificationContext';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        const MainApp = () => {
            const [loading, setLoading] = useState(false);

            useEffect(() => {
                const handleStart = () => setLoading(true);
                const handleFinish = () => setLoading(false);

                // Listen for Inertia navigation events
                router.on('start', handleStart);
                router.on('finish', handleFinish);

                // Cleanup event listeners
                return () => {
                    router.off('start', handleStart);
                    router.off('finish', handleFinish);
                };
            }, []);

            return (
                <>
                    {loading && <LoadingAnimation />}
                    <NotificationsProvider>
                        <App {...props} />
                    </NotificationsProvider>
                </>
            );
        };

        root.render(<MainApp {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
