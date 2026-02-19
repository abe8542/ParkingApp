import { Link, Head } from '@inertiajs/react';

export default function Welcome() {
    return (
        <>
            <Head title="Welcome to Parking System" />
            <div className="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-gray-100">
                <div className="max-w-7xl mx-auto p-6 lg:p-8 text-center">
                    <h1 className="text-4xl font-extrabold text-gray-900 mb-8">
                        Parking Management System
                    </h1>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {/* DRIVER PATH */}
                        <Link
                            href={route('parking.public')}
                            className="flex flex-col items-center p-10 bg-white border border-gray-200 rounded-lg shadow-md hover:bg-blue-50 transition"
                        >
                            <div className="text-5xl mb-4">🚗</div>
                            <h2 className="text-2xl font-bold text-blue-600">I am a Driver</h2>
                            <p className="text-gray-500 mt-2">Find your car, check fees, and pay.</p>
                        </Link>

                        {/* ADMIN PATH */}
                        <Link
                            href={route('login')}
                            className="flex flex-col items-center p-10 bg-white border border-gray-200 rounded-lg shadow-md hover:bg-gray-50 transition"
                        >
                            <div className="text-5xl mb-4">🔐</div>
                            <h2 className="text-2xl font-bold text-gray-800">I am an Admin</h2>
                            <p className="text-gray-500 mt-2">Manage slots and check-in vehicles.</p>
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
