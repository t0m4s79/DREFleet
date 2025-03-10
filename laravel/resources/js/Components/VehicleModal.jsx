import { Dialog, DialogPanel, Transition, TransitionChild } from '@headlessui/react';
import CloseIcon from '@mui/icons-material/Close';
import VehicleWarnings from './VehicleWarnings';
import { vehicleExpirations } from '@/utils/Dashboard/vehicles';
import { Link } from '@inertiajs/react';

export default function VehicleModal({ isOpen, onClose, vehicleModal, userType, maxWidth = '2xl' }) {
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
                            <h3 className="p-4 text-lg font-semibold">Veículos</h3>
                            <button onClick={close} className="text-gray-400 hover:text-gray-600">
                                <CloseIcon fontSize="small" className='mr-4' />
                            </button>
                        </div>

                        <div className="p-4">
                            {vehicleModal ? (
                                vehicleModal.length > 0 ? (
                                    vehicleModal.map((vehicle) => {
                                        const [expired, expiring] = vehicleExpirations(vehicle);

                                        return (
                                            <div key={`vehicle-${vehicle.id}`} className="flex items-center gap-3 pb-3">
                                                <Link href={route('vehicles.edit', vehicle)} className="hover:font-bold">
                                                    #{vehicle.id} - {vehicle.make} {vehicle.model} - {vehicle.license_plate}
                                                </Link>

                                                <VehicleWarnings expired={expired} expiring={expiring} vehicle={vehicle.id} userType={userType} />
                                            </div>
                                        )}
                                    )
                                ) : (
                                    <div className="text-gray-400">Nenhum veículo disponível.</div>
                                )
                            ) : (
                                <div className="text-gray-400">Carregando...</div>
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
