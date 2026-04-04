<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    booking: {
        type: Object,
        required: true
    },
    spot: {
        type: Object,
        required: true
    },
    vehicle: {
        type: Object,
        required: true
    }
});

const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    const options = { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric',
        hour: '2-digit', 
        minute: '2-digit' 
    };
    
    if (props.booking.timezone) {
        options.timeZone = props.booking.timezone;
    }
    
    return date.toLocaleString('en-US', options);
};

const durationUnits = computed(() => {
    const diffMs = new Date(props.booking.end_time).getTime() - new Date(props.booking.start_time).getTime();
    const diffMins = Math.ceil(diffMs / (1000 * 60));
    const units = Math.ceil(diffMins / 30);
    return units > 0 ? units : 1;
});

const mapsLink = computed(() => {
    if (props.spot.latitude && props.spot.longitude) {
        return `https://www.google.com/maps/search/?api=1&query=${props.spot.latitude},${props.spot.longitude}`;
    }
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(props.spot.address)}`;
});
</script>

<template>
    <Head title="Booking Details - Solopark" />

    <div class="min-h-screen bg-gray-50 font-sans text-gray-900 selection:bg-[#1866ed] selection:text-white pb-20">
        <!-- Assuming Navbar is available globally or imported correctly -->
        <Navbar :is-sticky="true" />

        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            
            <div class="mb-8 text-center sm:text-left">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4 sm:hidden">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900 flex items-center justify-center sm:justify-start">
                    <svg class="w-8 h-8 text-green-500 mr-3 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Reservation Confirmed!
                </h1>
                <p class="text-gray-500 mt-2 text-lg">Booking #{{ String(booking.id).padStart(6, '0') }}</p>
            </div>

            <div class="bg-white rounded-[20px] shadow-sm border border-gray-200 overflow-hidden mb-8">
                <!-- Spot Header Info -->
                <div class="p-6 sm:p-8 bg-gray-50 border-b border-gray-200 flex flex-col sm:flex-row items-center sm:items-start gap-6">
                    <img :src="spot.image" class="w-full sm:w-[160px] h-[120px] rounded-xl object-cover shadow-sm" alt="Parking Spot">
                    <div class="flex-1 text-center sm:text-left">
                        <span class="inline-block px-3 py-1 bg-blue-100 text-[#1866ed] text-xs font-bold rounded-full mb-3 uppercase tracking-wider">
                            {{ booking.status }}
                        </span>
                        <span v-if="booking.is_recurring" class="inline-block ml-2 px-3 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded-full mb-3 uppercase tracking-wider">
                            Recurring
                        </span>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ spot.title || spot.address }}</h2>
                        <p class="text-gray-500 mb-4 flex items-center justify-center sm:justify-start">
                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            {{ spot.address }}
                        </p>
                        <a :href="mapsLink" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-[14px] font-bold text-[#1866ed] hover:text-blue-800 transition-colors bg-blue-50 px-4 py-2 rounded-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                            Get Directions
                        </a>
                    </div>
                </div>

                <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
                    <!-- Left Column: Dates & Vehicle -->
                    <div class="space-y-8">
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Reservation Time</h3>
                            <div class="flex items-start">
                                <div class="bg-green-50 p-2 rounded-lg mr-4 text-green-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Start</p>
                                    <p class="text-base font-bold text-gray-900">{{ formatDateTime(booking.start_time) }}</p>
                                </div>
                            </div>
                            <div class="ml-5 border-l-2 border-dashed border-gray-200 h-6 my-1"></div>
                            <div class="flex items-start">
                                <div class="bg-red-50 p-2 rounded-lg mr-4 text-red-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">End</p>
                                    <p class="text-base font-bold text-gray-900">{{ formatDateTime(booking.end_time) }}</p>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100">

                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Your Vehicle</h3>
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 flex items-center">
                                <div class="bg-blue-100 p-2.5 rounded-full mr-4 text-[#1866ed]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[16px] font-extrabold text-gray-900 border border-gray-300 rounded px-2 py-0.5 inline-block bg-white shadow-sm mb-1 tracking-wider uppercase">{{ vehicle.license_plate }}</p>
                                    <p class="text-[14px] text-gray-500 font-medium">{{ vehicle.make_model }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="border-gray-100">

                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Owner Contact</h3>
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-[#1866ed] text-white flex items-center justify-center font-bold text-lg mr-4">
                                    {{ spot.owner.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <p class="text-base font-bold text-gray-900">{{ spot.owner.name }}</p>
                                    <p class="text-sm text-gray-500">{{ spot.owner.email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Payment & summary -->
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 h-fit">
                        <h3 class="text-lg font-bold text-gray-900 mb-6">Payment Summary</h3>
                        
                        <div class="space-y-4 mb-6">
                        </div>

                        <hr class="border-gray-200 mb-4">

                        <div class="flex justify-between items-center text-xl font-extrabold mb-8">
                            <span class="text-gray-900">Total Paid</span>
                            <span class="text-[#1866ed]">CA${{ Number(booking.total_price).toFixed(0) }}</span>
                        </div>
                        
                        <div class="text-center">
                            <Link :href="route('search')" class="block w-full py-3 px-4 border border-[#1866ed] rounded-xl text-[15px] font-bold text-[#1866ed] hover:bg-blue-50 transition-colors">
                                Find Another Spot
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help / Support Section -->
            <div class="text-center">
                <p class="text-gray-500 text-sm">Need help with your reservation? <Link :href="route('contact-us')" class="text-[#1866ed] font-medium hover:underline">Contact Support</Link></p>
            </div>
            
        </main>
    </div>
</template>
