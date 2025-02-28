import { Dialog, DialogPanel, DialogTitle, Transition, TransitionChild } from '@headlessui/react';
import { Fragment } from 'react';
import CloseIcon from '@mui/icons-material/Close';

const ErrorModal = ({ isOpen, onClose, errors }) => {

    return (
        <Transition appear show={isOpen} as={Fragment}>
            <Dialog as="div" className="relative z-[1000]" onClose={onClose}>
                <TransitionChild
                    as={Fragment}
                    enter="ease-out duration-300"
                    enterFrom="opacity-0 scale-95"
                    enterTo="opacity-100 scale-100"
                    leave="ease-in duration-200"
                    leaveFrom="opacity-100 scale-100"
                    leaveTo="opacity-0 scale-95"
                >
                    <div className="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm" />
                </TransitionChild>

                <div className="fixed inset-0 flex items-center justify-center p-4">
                    <TransitionChild
                        as={Fragment}
                        enter="ease-out duration-300"
                        enterFrom="opacity-0 scale-95"
                        enterTo="opacity-100 scale-100"
                        leave="ease-in duration-200"
                        leaveFrom="opacity-100 scale-100"
                        leaveTo="opacity-0 scale-95"
                    >
                        <DialogPanel className="w-full max-w-md bg-gray-800 text-white rounded-lg shadow-xl p-6">
                            <div className="flex justify-between items-center border-b pb-2">
                                <DialogTitle className="text-lg font-semibold">
                                    Erros Encontrados
                                </DialogTitle>
                                <button onClick={onClose} className="text-gray-400 hover:text-white">
                                    <CloseIcon fontSize="small" />
                                </button>
                            </div>

                            <div className="mt-4">
                                <ul className="list-none space-y-2 text-red-400">
                                    {Object.values(errors).map((messages, index) => (
                                        Array.isArray(messages) ? messages.map((msg, subIndex) => (
                                            <li key={subIndex} className="list-disc ml-4">{msg}</li>
                                        )) : <li key={index} className="list-disc ml-4">{messages}</li>
                                    ))}
                                </ul>
                            </div>

                            <div className="mt-6 flex justify-end">
                                <button
                                    onClick={onClose}
                                    className="px-4 py-2 bg-red-600 hover:bg-red-700 rounded-md"
                                >
                                    Fechar
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </Dialog>
        </Transition>
    );
};

export default ErrorModal;
