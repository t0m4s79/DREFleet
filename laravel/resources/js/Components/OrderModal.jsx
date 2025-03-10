import { Dialog, DialogPanel, Transition, TransitionChild } from '@headlessui/react';
import { Link } from '@inertiajs/react';
import CloseIcon from '@mui/icons-material/Close';
import { Chip } from '@mui/material';

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

export default function OrderModal({ isOpen, onClose, orderModal, maxWidth = '2xl', auth }) {
    let type = "";
    let data = [];

    if (orderModal && orderModal.length > 0) {
        type = orderModal[0].status;

        if (type === "Em curso" || type === "Interrompido") {
            data = orderModal.map((order) => {
                const orderLink = (auth.user.user_type === "Técnico" || auth.user.user_type === "Condutor")
                    ? order.status === "Em curso"
                        ? route('orders.showStopOrder', order)
                        : route('orders.showStartOrder', order)
                    : route('orders.edit', order);

                return (
                    <div key={`order-${order.id}`} className="mb-4">
                        <Link href={orderLink} className="hover:font-bold">
                            #{order.id} - {order.order_type} - {order.expected_begin_date} a {order.expected_end_date}
                            <span>{renderOrderStatus(order.status)}</span>
                        </Link>
                    </div>
                );
            });
        } else if (type === "Aprovado") {
            data = orderModal.map((order) => {
                const orderLink = (auth.user.user_type === "Técnico" || auth.user.user_type === "Condutor")
                    ? route('orders.showStartOrder', order)
                    : route('orders.edit', order);

                return (
                    <div key={`order-${order.id}`}>
                        <Link href={orderLink} className="hover:font-bold">
                            #{order.id} - {order.order_type} - {order.expected_begin_date} a {order.expected_end_date}
                        </Link>
                    </div>
                );
            });
        } else if (type === "Por aprovar") {
            data = orderModal.map((order) => {

                return (
                    <div>
                        {(auth.user.userType === "Técnico" || auth.user.userType === "Condutor") ? (
                            <Link href={route('orders.showStartOrder', order)} className="hover:font-bold">
                                #{order.id} - {order.order_type} - {order.expected_begin_date} a {order.expected_end_date}
                            </Link>
                        ) : (
                            <Link key={`order-${order.id}`} href={route('orders.edit', order)} className="hover:font-bold">
                                #{order.id} - {order.order_type} - {order.expected_begin_date} a {order.expected_end_date}
                            </Link>
                        )}
                    </div>
                );
            });
        }
    }

    const close = () => {
        onClose();
    };

    const maxWidthClass = {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[maxWidth];

    return (
        <Transition show={isOpen} leave="duration-200">
            <Dialog
                as="div"
                id="modal"
                className="fixed inset-0 flex overflow-y-auto px-4 py-6 sm:px-0 items-center z-50 transform transition-all"
                onClose={close}
            >
                <TransitionChild
                    enter="ease-out duration-300"
                    enterFrom="opacity-0"
                    enterTo="opacity-100"
                    leave="ease-in duration-200"
                    leaveFrom="opacity-100"
                    leaveTo="opacity-0"
                >
                    <div className="absolute inset-0 bg-gray-500/75" />
                </TransitionChild>

                <TransitionChild
                    enter="ease-out duration-300"
                    enterFrom="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enterTo="opacity-100 translate-y-0 sm:scale-100"
                    leave="ease-in duration-200"
                    leaveFrom="opacity-100 translate-y-0 sm:scale-100"
                    leaveTo="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <DialogPanel
                        className={`mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto ${maxWidthClass}`}
                    >
                        <div className="flex justify-between items-center border-b">
                            <h3 className="p-4 text-lg font-semibold">Pedidos {type}</h3>
                            <button onClick={close} className="text-gray-400 hover:text-gray-600">
                                <CloseIcon fontSize="small" className='mr-4' />
                            </button>
                        </div>

                        <div className="p-4">
                            {data.length > 0 ? (
                                data
                            ) : (
                                <div className="text-gray-400">Nenhum pedido disponível.</div>
                            )}
                        </div>

                        <div className="p-4 flex justify-end">
                            <button
                                onClick={close}
                                className="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md"
                            >
                                Fechar
                            </button>
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </Dialog>
        </Transition>
    );
}
