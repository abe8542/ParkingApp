import React, { useEffect } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';

// Fix for default Leaflet icon disappearing in React builds
import L from 'leaflet';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
let DefaultIcon = L.icon({ iconUrl: markerIcon, shadowUrl: markerShadow, iconSize: [25, 41], iconAnchor: [12, 41] });
L.Marker.prototype.options.icon = DefaultIcon;

export default function PublicParking({ available_slots, occupied_slots, searchResult }) {
    const { data, setData, post, processing, errors } = useForm({
        plate_number: '',
    });

    const totalSlots = 50;
    const slots = Array.from({ length: totalSlots }, (_, i) => i + 1);

    /**
     * AUTO-REFRESH LOGIC
     * Pings the server every 5s if a car is found but not yet paid.
     */
    useEffect(() => {
        let interval = null;
        if (searchResult && searchResult.status === 'parked') {
            interval = setInterval(() => {
                router.reload({
                    only: ['searchResult', 'available_slots', 'occupied_slots'],
                    preserveScroll: true
                });
            }, 5000);
        }
        return () => { if (interval) clearInterval(interval); };
    }, [searchResult]);

    const handleSearch = (e) => {
        e.preventDefault();
        post(route('vehicles.search'), { preserveScroll: true });
    };

    const handleExit = (id) => {
        if (confirm('Are you at the exit gate? This will release your parking slot.')) {
            router.post(route('vehicles.exit', id));
        }
    };

    // Logical Check for UI State
    const isPaid = searchResult?.status === 'paid';
    const isFreeGrace = searchResult?.current_fee === 0;
    const canExit = searchResult && (isPaid || isFreeGrace);

    return (
        <div className="min-h-screen bg-slate-50 pb-12 font-sans">
            <Head title="Driver Portal | SmartPark" />

            {/* Premium Header */}
            <div className="bg-gradient-to-r from-blue-900 to-indigo-900 text-white py-12 px-4 shadow-2xl mb-8">
                <div className="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                    <div className="text-center md:text-left">
                        <h1 className="text-4xl font-black tracking-tighter uppercase italic">SmartPark Nairobi</h1>
                        <p className="text-blue-200 text-sm mt-1">Automated Parking Management System</p>
                    </div>
                    <div className="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl flex items-center gap-4">
                        <div className="text-right">
                            <p className="text-[10px] uppercase font-bold text-blue-200">Current Availability</p>
                            <p className="text-2xl font-black">{available_slots} / {totalSlots}</p>
                        </div>
                        <div className="h-10 w-[2px] bg-white/20"></div>
                        <div className="relative flex h-4 w-4">
                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span className="relative inline-flex rounded-full h-4 w-4 bg-green-500 border-2 border-white"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div className="max-w-6xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-12 gap-8">

                {/* Left Column: Interactions (4 Cols) */}
                <div className="lg:col-span-4 space-y-6">
                    {/* Search Section */}
                    <div className="bg-white p-8 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100">
                        <h2 className="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                            🔍 Locate My Vehicle
                        </h2>
                        <form onSubmit={handleSearch} className="space-y-4">
                            <div className="relative">
                                <input
                                    type="text"
                                    placeholder="KDR 118Y"
                                    className="w-full p-5 bg-slate-50 border-2 border-slate-100 rounded-2xl font-black text-center text-3xl uppercase tracking-widest focus:border-blue-500 focus:bg-white transition-all outline-none"
                                    value={data.plate_number}
                                    onChange={e => setData('plate_number', e.target.value)}
                                />
                                {errors.plate_number && <p className="text-red-500 text-xs mt-2 font-bold">{errors.plate_number}</p>}
                            </div>
                            <button disabled={processing} className="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-5 rounded-2xl transition-all shadow-lg shadow-blue-200 active:scale-95">
                                {processing ? 'LOCATING...' : 'VERIFY STATUS'}
                            </button>
                        </form>
                    </div>

                    {/* Enhanced Result Card */}
                    {searchResult && (
                        <div className="bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100 animate-in fade-in slide-in-from-bottom-8 duration-500">
                            <div className={`p-1 text-center text-[10px] font-black uppercase tracking-[0.2em] text-white ${isPaid ? 'bg-green-500' : 'bg-amber-500'}`}>
                                {isPaid ? 'Payment Verified' : 'Action Required'}
                            </div>

                            <div className="p-8">
                                <div className="flex justify-between items-center mb-8">
                                    <div className="bg-slate-100 px-4 py-2 rounded-xl">
                                        <p className="text-[10px] text-slate-400 font-bold uppercase">Plate</p>
                                        <p className="font-black text-slate-700">{searchResult.plate_number}</p>
                                    </div>
                                    <div className="bg-blue-50 px-4 py-2 rounded-xl text-right">
                                        <p className="text-[10px] text-blue-400 font-bold uppercase">Slot</p>
                                        <p className="font-black text-blue-600">#{searchResult.slot_number}</p>
                                    </div>
                                </div>

                                <div className="text-center py-6 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 mb-6">
                                    <p className="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Due</p>
                                    <h3 className="text-5xl font-black text-slate-900">
                                        <span className="text-xl mr-1 text-slate-400">KES</span>
                                        {searchResult.current_fee}
                                    </h3>
                                </div>

                                <div className="space-y-3">
                                    {/* M-PESA Action */}
                                    {!isPaid && searchResult.current_fee > 0 && (
                                        <button
                                            onClick={() => router.post(route('vehicles.pay', searchResult.id))}
                                            className="w-full bg-[#24c047] hover:bg-[#1da83d] text-white font-black py-4 rounded-2xl flex items-center justify-center gap-3 shadow-lg shadow-green-100 transition-all"
                                        >
                                            <span className="bg-white text-[#24c047] rounded-md px-2 py-0.5 text-xs">M</span>
                                            PAY VIA M-PESA
                                        </button>
                                    )}

                                    {/* Receipt Download */}
                                    {canExit && (
                                        <a
                                            href={route('vehicles.receipt', searchResult.id)}
                                            target="_blank"
                                            className="flex items-center justify-center gap-2 w-full py-3 text-blue-600 font-bold text-sm bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors"
                                        >
                                            📄 View Digital Receipt
                                        </a>
                                    )}

                                    {/* Final Exit Button */}
                                    <button
                                        onClick={() => handleExit(searchResult.id)}
                                        disabled={!canExit}
                                        className={`w-full font-black py-5 rounded-2xl transition-all shadow-xl ${
                                            canExit
                                            ? 'bg-slate-900 text-white hover:bg-black'
                                            : 'bg-slate-100 text-slate-300 cursor-not-allowed shadow-none'
                                        }`}
                                    >
                                        {canExit ? 'RELEASE SLOT & EXIT' : 'EXIT LOCKED'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Right Column: Visual Mapping (8 Cols) */}
                <div className="lg:col-span-8 space-y-8">
                    {/* Leaflet Map Integration */}
                    <div className="bg-white p-4 rounded-[2rem] shadow-xl border border-slate-100 overflow-hidden h-[400px]">
                        <MapContainer center={[-1.286389, 36.817223]} zoom={15} style={{ height: '100%', width: '100%', borderRadius: '1.5rem' }}>
                            <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
                            <Marker position={[-1.286389, 36.817223]}>
                                <Popup>SmartPark Nairobi - Entrance</Popup>
                            </Marker>
                        </MapContainer>
                    </div>

                    {/* Detailed Yard Overview */}
                    <div className="bg-white p-10 rounded-[2rem] shadow-xl border border-slate-100">
                        <div className="flex flex-col md:flex-row justify-between items-center mb-10 gap-4">
                            <div>
                                <h3 className="text-xl font-black text-slate-900 uppercase italic tracking-tighter">Live Yard Map</h3>
                                <p className="text-slate-400 text-xs font-bold">Real-time occupancy visualization</p>
                            </div>
                            <div className="flex bg-slate-50 p-2 rounded-xl gap-6 border border-slate-100">
                                <div className="flex items-center gap-2 px-2">
                                    <div className="w-3 h-3 bg-green-500 rounded-full shadow-[0_0_10px_rgba(34,197,94,0.5)]"></div>
                                    <span className="text-[10px] font-black uppercase text-slate-500 tracking-widest">Available</span>
                                </div>
                                <div className="flex items-center gap-2 px-2">
                                    <div className="w-3 h-3 bg-slate-200 rounded-full"></div>
                                    <span className="text-[10px] font-black uppercase text-slate-500 tracking-widest">Occupied</span>
                                </div>
                            </div>
                        </div>

                        <div className="grid grid-cols-5 sm:grid-cols-10 gap-4">
                            {slots.map(num => (
                                <div key={num} className={`aspect-square rounded-2xl flex items-center justify-center text-[10px] font-black transition-all transform hover:scale-110 cursor-default ${
                                    occupied_slots.includes(num)
                                    ? 'bg-slate-100 text-slate-300 border border-slate-200/50'
                                    : 'bg-green-500 text-white shadow-lg shadow-green-200'
                                }`}>
                                    {num}
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
