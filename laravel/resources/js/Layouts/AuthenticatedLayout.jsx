import { useState, useEffect } from 'react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link } from '@inertiajs/react';
import NotificationsIcon from '@mui/icons-material/Notifications';

export default function Authenticated({ user, header, children }) {
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

    const [unreadCount, setUnreadCount] = useState(0);

    useEffect(() => {
        // Fetch the unread notifications count from the back-end
        const fetchUnreadCount = async () => {
            try {
                const response = await axios.get('/notifications/unread-count');
                setUnreadCount(response.data.unread_count);

            } catch (error) {
                console.error('Error fetching unread notifications count', error);
            }
        };

        fetchUnreadCount();
    }, []);

    return (
        <div className="min-h-screen bg-sky-100">
            <nav className="bg-white border-b border-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <div className="shrink-0 flex items-center w-14">
                                <Link href="/">
                                    <ApplicationLogo className="block h-9 w-auto fill-current text-gray-800" />
                                </Link>
                            </div>

                            <div className="hidden space-x-8 sm:-my-px sm:ms-10 md:flex">
                                <NavLink href={route('dashboard.index')} active={route().current('dashboard.index')}>
                                    Painel de Controlo
                                </NavLink>

                                <div className="hidden sm:flex sm:items-center sm:ms-6">
                                    <div className="relative">                                
                                        <Dropdown>
                                            <Dropdown.Trigger>
                                                <span className="inline-flex rounded-md">
                                                    <button
                                                        type="button"
                                                        className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                                    >
                                                        Veículos

                                                        <svg
                                                            className="ms-2 -me-0.5 h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fillRule="evenodd"
                                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                                clipRule="evenodd"
                                                            />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </Dropdown.Trigger>

                                            <Dropdown.Content>
                                                <Dropdown.Link  href={route('vehicles.index')} active={route().current('vehicles.index')}>Todos os Veículos</Dropdown.Link>
                                                <Dropdown.Link  href={route('vehicleDocuments.index')} active={route().current('vehicleDocuments.index')}>Documentos</Dropdown.Link>
                                                <Dropdown.Link  href={route('vehicleAccessories.index')} active={route().current('vehicleAccessories.index')}>Acessórios</Dropdown.Link>
                                            </Dropdown.Content>
                                        </Dropdown>
                                    </div>
                                </div>


                                <div className="hidden sm:flex sm:items-center sm:ms-6">
                                    <div className="relative">                                
                                        <Dropdown>
                                            <Dropdown.Trigger>
                                                <span className="inline-flex rounded-md">
                                                    <button
                                                        type="button"
                                                        className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                                    >
                                                        Utilizadores

                                                        <svg
                                                            className="ms-2 -me-0.5 h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fillRule="evenodd"
                                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                                clipRule="evenodd"
                                                            />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </Dropdown.Trigger>

                                            <Dropdown.Content>
                                                <Dropdown.Link  href={route('users.index')} active={route().current('users.index')}>Todos os Utilizadores</Dropdown.Link>
                                                <Dropdown.Link  href={route('drivers.index')} active={route().current('drivers.index')}>Condutores</Dropdown.Link>
                                                <Dropdown.Link  href={route('technicians.index')} active={route().current('technicians.index')}>Técnicos</Dropdown.Link>
                                                <Dropdown.Link  href={route('managers.index')} active={route().current('managers.index')}>Gestores</Dropdown.Link>

                                                {/* TODO: Links para: */}
                                                {/* 2º) ADMINISTRADORES */}
                                                {/* 5º) POR ATRIBUIR */}
                                            </Dropdown.Content>
                                        </Dropdown>
                                    </div>
                                </div>

                                <NavLink href={route('kids.index')} active={route().current('kids.index')}>
                                    Crianças
                                </NavLink>

                                <NavLink href={route('places.index')} active={route().current('places.index')}>
                                    Moradas
                                </NavLink>

                                <div className="hidden sm:flex sm:items-center sm:ms-6">
                                    <div className="relative">                                
                                        <Dropdown>
                                            <Dropdown.Trigger>
                                                <span className="inline-flex rounded-md">
                                                    <button
                                                        type="button"
                                                        className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                                    >
                                                        Pedidos

                                                        <svg
                                                            className="ms-2 -me-0.5 h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fillRule="evenodd"
                                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                                clipRule="evenodd"
                                                            />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </Dropdown.Trigger>

                                            <Dropdown.Content>
                                                <Dropdown.Link  href={route('orders.index')} active={route().current('orders.index')}>Todos os Pedidos</Dropdown.Link>
                                                <Dropdown.Link  href={route('orderOccurrences.index')} active={route().current('orderOccurrences.index')}>Ocorrências</Dropdown.Link>
                                            </Dropdown.Content>
                                        </Dropdown>
                                    </div>
                                </div>

                                <NavLink href={route('orderRoutes.index')} active={route().current('orderRoutes.index')}>
                                    Rotas
                                </NavLink>
                            </div>
                        </div>

                        <div className="hidden md:flex sm:items-center sm:ms-6">
                            <Dropdown>
                                <Dropdown.Trigger>
                                    <span className="inline-flex rounded-md relative">
                                        <button
                                            type="button"
                                            className="relative inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                        >
                                            {/* Bell Icon */}
                                            <NotificationsIcon />

                                            {/* Badge: Unread Count */}
                                            {unreadCount > 0 && (
                                                <span className="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full transform translate-x-1/2 -translate-y-1/2">
                                                    {unreadCount}
                                                </span>
                                            )}
                                        </button>
                                    </span>
                                </Dropdown.Trigger>

                                <Dropdown.Content>
                                    <Dropdown.Link href={route('notifications.index')}>
                                        Minhas Notificações
                                    </Dropdown.Link>
                                </Dropdown.Content>
                            </Dropdown>

                            <div className="ms-3 relative">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {user.name}

                                                <svg
                                                    className="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>

                                    <Dropdown.Content>
                                        <Dropdown.Link href={route('profile.edit')}>Meu Perfil</Dropdown.Link>
                                        <Dropdown.Link href={route('logout')} method="post" as="button">
                                            Terminar Sessão
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>

                        <div className="-me-2 flex items-center sm:hidden">
                            <button
                                onClick={() => setShowingNavigationDropdown((previousState) => !previousState)}
                                className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg className="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        className={!showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        className={showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div className={(showingNavigationDropdown ? 'block' : 'hidden') + ' sm:hidden'}>
                    <div className="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink href={route('dashboard.index')} active={route().current('dashboard.index')}>
                            Painel de Controlo
                        </ResponsiveNavLink>

                        <ResponsiveNavLink href={route('vehicles.index')} active={route().current('vehicles.index')}>
                            Veículos
                        </ResponsiveNavLink>

                        <ResponsiveNavLink href={route('users.index')} active={route().current('users.index')}>
                            Todos os Utilizadores
                        </ResponsiveNavLink>

                        <ResponsiveNavLink href={route('drivers.index')} active={route().current('drivers.index')}>
                            Condutores
                        </ResponsiveNavLink>

                        <ResponsiveNavLink href={route('technicians.index')} active={route().current('technicians.index')}>
                            Técnicos
                        </ResponsiveNavLink>

                        <ResponsiveNavLink href={route('managers.index')} active={route().current('managers.index')}>
                            Gestores
                        </ResponsiveNavLink>

                        <ResponsiveNavLink href={route('kids.index')} active={route().current('kids.index')}>
                            Crianças
                        </ResponsiveNavLink>

                        <ResponsiveNavLink href={route('places.index')} active={route().current('places.index')}>
                            Moradas
                        </ResponsiveNavLink>
                    </div>

                    <div className="pt-4 pb-1 border-t border-gray-200">
                        <div className="px-4">
                            <div className="font-medium text-base text-gray-800">{user.name}</div>
                            <div className="font-medium text-sm text-gray-500">{user.email}</div>
                        </div>

                        <div className="mt-3 space-y-1">
                            <ResponsiveNavLink href={route('profile.edit')}>Meu Perfil</ResponsiveNavLink>
                            <ResponsiveNavLink method="post" href={route('logout')} as="button">
                                Terminar Sessão
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="bg-white shadow">
                    <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">{header}</div>
                </header>
            )}

            <main>{children}</main>
        </div>
    );
}
