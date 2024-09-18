import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function Guest({ children }) {
    return (
        <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-sky-100">
            
            <div className="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                <div >
                    <Link href="/">
                        <img className="m-auto pt-8 pb-10 w-60 fill-current text-gray-500" src="/img/login_logo.svg" alt="App Login Logo" />
                    </Link>
                </div>

                <div >
                    {children}
                </div>

            </div>
        </div>
    );
}
