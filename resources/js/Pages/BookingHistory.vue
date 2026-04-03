<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    bookings: {
        type: Array,
        default: () => []
    }
});

const formatDateTime = (dateString, timezone = null) => {
    const date = new Date(dateString);
    const options = { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    };
    
    if (timezone) {
        options.timeZone = timezone;
    }
    
    return date.toLocaleString('en-US', options);
};
</script>

<template>
    <Head title="My Bookings - Solopark" />

    <div class="min-h-screen bg-gray-50 font-sans text-gray-900 selection:bg-[#1866ed] selection:text-white pb-20">
        <Navbar :is-sticky="true" />

        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">My Bookings</h1>
                    <p class="text-gray-500 mt-1">Manage and view your parking reservations</p>
                </div>
                <Link :href="route('search')" class="inline-flex items-center text-[15px] font-bold text-[#1866ed] hover:text-blue-800 transition">
                    Book another spot
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </Link>
            </div>

            <div v-if="bookings.length === 0" class="bg-white rounded-[20px] shadow-sm border border-gray-200 p-12 text-center">
                <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">No bookings found</h2>
                <p class="text-gray-500 mb-8 max-w-xs mx-auto">You haven't made any parking reservations yet.</p>
                <Link :href="route('search')" class="inline-flex bg-[#1866ed] hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl transition-colors">
                    Find Parking Now
                </Link>
            </div>

            <div v-else class="space-y-6">
                <div v-for="booking in bookings" :key="booking.id" class="bg-white rounded-[20px] shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row">
                        <!-- Image -->
                        <div class="md:w-[240px] h-[160px] md:h-auto overflow-hidden">
                            <img :src="booking.spot.image" class="w-full h-full object-cover" alt="Parking Spot">
                        </div>
                        
                        <!-- Details -->
                        <div class="flex-1 p-6 sm:p-8 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span class="inline-block px-2.5 py-0.5 bg-blue-100 text-[#1866ed] text-[11px] font-bold rounded-full mb-2 uppercase tracking-wider">
                                            {{ booking.status }}
                                        </span>
                                        <span v-if="booking.is_recurring" class="inline-block ml-2 px-2.5 py-0.5 bg-purple-100 text-purple-700 text-[11px] font-bold rounded-full mb-2 uppercase tracking-wider">
                                            Recurring
                                        </span>
                                        <h3 class="text-xl font-bold text-gray-900">{{ booking.spot.title || booking.spot.address }}</h3>
                                        <p class="text-gray-500 text-sm flex items-center mt-1">
                                            <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            {{ booking.spot.address }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[13px] text-gray-400 font-bold uppercase tracking-wider mb-1">Total Paid</p>
                                        <p class="text-xl font-extrabold text-[#1a1a1a]">CA${{ Number(booking.total_price).toFixed(0) }}</p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                                    <div class="flex items-center text-sm">
                                        <div class="bg-gray-50 p-2 rounded-lg mr-3 text-gray-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-gray-400 text-[11px] font-bold tracking-wider uppercase">From</p>
                                            <p class="font-bold text-gray-700">{{ formatDateTime(booking.start_time, booking.timezone) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <div class="bg-gray-50 p-2 rounded-lg mr-3 text-gray-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-gray-400 text-[11px] font-bold tracking-wider uppercase">To</p>
                                            <p class="font-bold text-gray-700">{{ formatDateTime(booking.end_time, booking.timezone) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-8 flex justify-end">
                                <Link :href="route('bookings.show', booking.id)" class="text-[14px] font-bold text-[#1866ed] hover:text-blue-800 transition-colors bg-blue-50 px-6 py-2.5 rounded-lg flex items-center">
                                    View Details
                                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
