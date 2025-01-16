import React, { useState } from 'react';
import { Drawer, List, ListItemButton, ListItemText, Collapse, IconButton, Divider, Button, SwipeableDrawer, Avatar } from '@mui/material';
import { Menu as MenuIcon, ExpandLess, ExpandMore, Close } from '@mui/icons-material';
import ApplicationLogo from './ApplicationLogo';
import { Link } from '@inertiajs/react';

function ResponsiveDrawer({ user }) {
    const [isDrawerOpen, setDrawerOpen] = useState(false);
    const [openSubmenus, setOpenSubmenus] = useState({});

    // Menu configuration
    const NAV_LINKS = [
        { label: 'Painel de Controlo', href: route('dashboard.index'), active: route().current('dashboard.index') },
        {
            label: 'Veículos',
            children: [
                { label: 'Todos os Veículos', href: route('vehicles.index'), active: route().current('vehicles.index') },
                { label: 'Documentos', href: route('vehicleDocuments.index'), active: route().current('vehicleDocuments.index') },
                { label: 'Acessórios', href: route('vehicleAccessories.index'), active: route().current('vehicleAccessories.index') },
            ],
        },
        {
            label: 'Utilizadores',
            children: [
                { label: 'Todos os Utilizadores', href: route('users.index'), active: route().current('users.index') },
                { label: 'Condutores', href: route('drivers.index'), active: route().current('drivers.index') },
                { label: 'Técnicos', href: route('technicians.index'), active: route().current('technicians.index') },
                { label: 'Gestores', href: route('managers.index'), active: route().current('managers.index') },
            ],
        },
        { label: 'Crianças', href: route('kids.index'), active: route().current('kids.index') },
        { label: 'Moradas', href: route('places.index'), active: route().current('places.index') },
        {
            label: 'Pedidos',
            children: [
                { label: 'Todos os Pedidos', href: route('orders.index'), active: route().current('orders.index') },
                { label: 'Ocorrências', href: route('orderOccurrences.index'), active: route().current('orderOccurrences.index') },
            ],
        },
        { label: 'Rotas', href: route('orderRoutes.index'), active: route().current('orderRoutes.index')},
    ];

    // Toggle drawer
    const toggleDrawer = () => setDrawerOpen(!isDrawerOpen);

    // Toggle submenu
    const toggleSubmenu = (menu) => {
        setOpenSubmenus((prev) => ({ ...prev, [menu]: !prev[menu] }));
    };

    return (
        <>
            {/* Menu Icon to open Drawer */}
            <IconButton edge="start" color="inherit" aria-label="menu" onClick={toggleDrawer}>
                <MenuIcon />
            </IconButton>

            {/* Drawer */}
            <SwipeableDrawer anchor="left" open={isDrawerOpen} onClose={toggleDrawer}>
                <div className="xs:w-40 sm:w-80">
                    <List>
                        <div className="w-24 mb-4 ml-4">
                            <Link href="/">
                                <ApplicationLogo className="block h-9 w-auto fill-current text-gray-800" />
                            </Link>
                        </div>

                        <ListItemButton onClick={toggleDrawer} sx={{color: 'red'}}>
                            <Close/> Fechar
                        </ListItemButton>

                        {NAV_LINKS.map((link, index) => (
                            <React.Fragment key={index}>
                                {/* Parent Menu Item */}
                                {!link.children ? (
                                    <ListItemButton
                                        component="a"
                                        href={link.href}
                                        selected={link.active}
                                        onClick={toggleDrawer} // Close drawer on link click
                                    >
                                        <ListItemText primary={link.label} />
                                    </ListItemButton>
                                ) : (
                                    <>
                                        <ListItemButton onClick={() => toggleSubmenu(link.label)}>
                                            <ListItemText primary={link.label} />
                                            {openSubmenus[link.label] ? <ExpandLess /> : <ExpandMore />}
                                        </ListItemButton>
                                        {/* Submenu Items */}
                                        <Collapse in={openSubmenus[link.label]} timeout="auto" unmountOnExit>
                                            <List component="div" disablePadding>
                                                {link.children.map((child, childIndex) => (
                                                    <ListItemButton
                                                        key={childIndex}
                                                        component="a"
                                                        href={child.href}
                                                        selected={child.active}
                                                        onClick={toggleDrawer} // Close drawer on link click
                                                        sx={{ pl: 4 }}
                                                    >
                                                        <ListItemText primary={child.label} />
                                                    </ListItemButton>
                                                ))}
                                            </List>
                                        </Collapse>
                                    </>
                                )}
                            </React.Fragment>
                        ))}
                    </List>

                    {/* Divider */}
                    <Divider />

                    {/* User Info Section */}
                    <div className="p-4 flex">
                        <Avatar alt='Utilizador' sx={{ width: 24, height: 24, marginRight: 2}}/>
                        <div className="font-medium text-base text-gray-800">{user.name}</div>
                    </div>

                    {/* Profile and Logout */}
                    <List>
                        <ListItemButton component="a" href={route('notifications.index')}>
                            <ListItemText primary="Minhas Notificações"/>
                        </ListItemButton>
                        <ListItemButton component="a" href={route('profile.edit')} onClick={toggleDrawer}>
                            <ListItemText primary="Meu Perfil" />
                        </ListItemButton>
                        <ListItemButton component="a" href={route('logout')} onClick={toggleDrawer}>
                            <ListItemText primary="Terminar Sessão" />
                        </ListItemButton>
                    </List>
                </div>
            </SwipeableDrawer>
        </>
    );
}

export default ResponsiveDrawer;
