import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';

export default function Dashboard({ auth, vehicles, stats }) {
    const { data, setData, post, processing, reset, errors } = useForm({
        plate_number: '',
        phone_number: '254',
        slot_number: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('vehicles.store'), {
            onSuccess: () => reset(),
        });
    };

    const handleManualPay = (id) => {
        if (confirm('Mark this vehicle as PAID manually (Cash)?')) {
            router.post(route('vehicles.manual-pay', id));
        }
    };

    const handleForceDelete = (id) => {
        if (confirm('This will free the slot immediately. Proceed?')) {
            router.delete(route('vehicles.force-delete', id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Admin Parking Control</h2>}
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    {/* STATS CARDS */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div className="bg-white p-6 rounded-lg shadow border-b-4 border-blue-500">
                            <p className="text-gray-500 text-sm uppercase font-bold">Total Capacity</p>
                            <p className="text-3xl font-black">{stats.total}</p>
                        </div>
                        <div className="bg-white p-6 rounded-lg shadow border-b-4 border-red-500">
                            <p className="text-gray-500 text-sm uppercase font-bold">Occupied</p>
                            <p className="text-3xl font-black">{stats.occupied}</p>
                        </div>
                        <div className="bg-white p-6 rounded-lg shadow border-b-4 border-green-500">
                            <p className="text-gray-500 text-sm uppercase font-bold">Available</p>
                            <p className="text-3xl font-black">{stats.available}</p>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* CHECK-IN FORM */}
                        <div className="bg-white p-6 rounded-lg shadow">
                            <h3 className="font-bold text-lg mb-4">New Check-In</h3>
                            <form onSubmit={submit} className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Plate Number</label>
                                    <input type="text" className="w-full mt-1 border-gray-300 rounded-md shadow-sm uppercase"
                                        value={data.plate_number} onChange={e => setData('plate_number', e.target.value)} />
                                    {errors.plate_number && <div className="text-red-500 text-xs mt-1">{errors.plate_number}</div>}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Phone (254...)</label>
                                    <input type="text" className="w-full mt-1 border-gray-300 rounded-md shadow-sm"
                                        value={data.phone_number} onChange={e => setData('phone_number', e.target.value)} />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Slot #</label>
                                    <input type="number" className="w-full mt-1 border-gray-300 rounded-md shadow-sm"
                                        value={data.slot_number} onChange={e => setData('slot_number', e.target.value)} />
                                </div>
                                <button disabled={processing} className="w-full bg-blue-600 text-white py-2 rounded-md font-bold hover:bg-blue-700">
                                    {processing ? 'Processing...' : 'Park Vehicle'}
                                </button>
                            </form>
                        </div>

                        {/* LIVE VEHICLE LIST */}
                        <div className="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slot/Plate</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fee</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {vehicles.map((vehicle) => (
                                        <tr key={vehicle.id}>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm font-bold text-gray-900">{vehicle.plate_number}</div>
                                                <div className="text-xs text-gray-500">Slot: {vehicle.slot_number}</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                                    vehicle.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                                }`}>
                                                    {vehicle.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600">
                                                KES {vehicle.current_fee}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                {vehicle.status === 'parked' && (
                                                    <button onClick={() => handleManualPay(vehicle.id)} className="text-green-600 hover:text-green-900 font-bold">Manual Pay</button>
                                                )}
                                                <button onClick={() => handleForceDelete(vehicle.id)} className="text-red-600 hover:text-red-900">Release Slot</button>
                                            </td>
                                        </tr>
                                    ))}
                                    {vehicles.length === 0 && (
                                        <tr>
                                            <td colSpan="4" className="px-6 py-10 text-center text-gray-400">Yard is empty.</td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
