<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    spots: {
        type: Array,
        default: () => []
    }
});

const selectedSpotId = ref(null);
const isModalOpen = ref(false);

const selectedSpot = computed(() => {
    if (!selectedSpotId.value) return null;
    return props.spots.find(s => s.id === selectedSpotId.value) || null;
});

const openSpotModal = (spot) => {
    selectedSpotId.value = spot.id;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        selectedSpotId.value = null;
    }, 300);
};

const toggleStatus = (spotId) => {
    router.patch(route('spots.toggle-status', spotId), {}, {
        preserveScroll: true,
    });
};

const deleteSpot = (spotId) => {
    if (confirm('Are you sure you want to delete this parking spot? This action cannot be undone.')) {
        router.delete(route('spots.destroy', spotId), {
            onSuccess: () => closeModal(),
        });
    }
};

const editSpot = (spotId) => {
    router.get(route('spots.edit', spotId));
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString(undefined, options);
};

const getDurationUnits = (start, end) => {
    if (!start || !end) return 0;
    const ms = new Date(end) - new Date(start);
    return Math.ceil(ms / (1000 * 60 * 30));
};

const calculateTotalEarnings = (bookings) => {
    if (!bookings) return '0';
    return bookings.reduce((sum, b) => sum + parseFloat(b.total_price || 0), 0).toFixed(0);
};

const upcomingBookings = computed(() => {
    if (!selectedSpot.value || !selectedSpot.value.bookings) return [];
    const now = new Date();
    return selectedSpot.value.bookings.filter(b => new Date(b.start_time) > now);
});

const pastBookings = computed(() => {
    if (!selectedSpot.value || !selectedSpot.value.bookings) return [];
    const now = new Date();
    return selectedSpot.value.bookings.filter(b => new Date(b.start_time) <= now);
});
</script>

<template>

    <Head title="My Listings - Solopark" />

    <div class="min-h-screen bg-gray-50 font-sans text-gray-900 selection:bg-[#1866ed] selection:text-white pb-20">
        <Navbar :is-sticky="true" />

        <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            <div class="mb-10 flex flex-col sm:flex-row sm:items-end justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">My Listings</h1>
                    <p class="text-gray-500 mt-1">Manage all your listed parking spots</p>
                </div>
                <Link :href="route('list-spot')"
                    class="inline-flex bg-[#1866ed] hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-blue-200 transition-all hover:-translate-y-1">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add a New Spot
                </Link>
            </div>

            <div v-if="spots.length === 0"
                class="bg-white rounded-[20px] shadow-sm border border-gray-200 p-16 text-center">
                <div class="bg-blue-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-8">
                    <svg class="w-12 h-12 text-[#1866ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-extrabold text-gray-900 mb-2">No listings yet</h2>
                <p class="text-gray-500 mb-8 max-w-sm mx-auto">Make passive income by listing your empty driveway or
                    garage. It only takes a few minutes.</p>
                <Link :href="route('list-spot')"
                    class="inline-flex items-center text-[16px] font-bold text-[#1866ed] hover:text-blue-800 transition">
                    Get started now
                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </Link>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="spot in spots" :key="spot.id" @click="openSpotModal(spot)"
                    class="bg-white rounded-[24px] overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 transform group cursor-pointer">
                    <!-- Image with overlay -->
                    <div class="relative h-56 overflow-hidden">
                        <img :src="spot.image"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            alt="Parking Spot">
                        <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/60 to-transparent">
                            <span :class="[
                                'inline-flex items-center backdrop-blur-md text-white text-[12px] font-bold px-3 py-1 rounded-full uppercase tracking-widest leading-none',
                                spot.is_active ? 'bg-green-500/50' : 'bg-red-500/50'
                            ]">
                                {{ spot.is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </div>
                        <div class="absolute top-4 right-4 flex flex-col items-end gap-2">
                            <div class="bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-xl border border-white font-bold text-gray-900 text-base shadow-lg">
                                CA${{ spot.price_hourly }}<span class="text-[10px] font-medium text-gray-500 ml-0.5">/hr</span>
                            </div>
                            <div v-if="spot.price_monthly" class="bg-[#1866ed]/90 backdrop-blur-sm px-3 py-1.5 rounded-xl border border-blue-400 font-bold text-white text-base shadow-lg">
                                CA${{ spot.price_monthly }}<span class="text-[10px] font-medium text-blue-100 ml-0.5">/mo</span>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <h3 class="text-xl font-extrabold text-gray-900 mb-2 line-clamp-1 h-7">
                            {{ spot.title || spot.address }}
                        </h3>
                        <p class="text-gray-500 text-sm flex items-center mb-6">
                            <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="line-clamp-1">{{ spot.address }}</span>
                        </p>

                        <div class="flex items-center justify-between pt-5 border-t border-gray-50">
                            <div class="flex items-center text-[14px] font-bold text-[#1866ed]">
                                {{ spot.bookings.length }} Bookings
                            </div>

                            <button
                                class="text-[14px] font-bold text-[#1866ed] hover:text-blue-800 transition flex items-center group">
                                Manage Spot
                                <svg class="w-4 h-4 ml-1.5 transform group-hover:translate-x-1 transition-transform"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Details Modal -->
        <div v-if="isModalOpen && selectedSpot" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 lg:p-8">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>

            <div class="relative bg-white w-full max-w-4xl max-h-[90vh] rounded-[32px] shadow-2xl overflow-hidden flex flex-col animate-in fade-in zoom-in duration-300">
                <!-- Modal Header -->
                <div class="p-6 md:p-8 border-b border-gray-100 flex items-center justify-between bg-white sticky top-0 z-10">
                    <div class="flex items-center gap-4">
                        <div class="bg-blue-50 p-3 rounded-2xl">
                            <svg class="w-6 h-6 text-[#1866ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-10V4m0 10V4m0 10h1m-1 4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 leading-none">
                                {{ selectedSpot.title || 'Spot Details' }}
                            </h2>
                            <p class="text-gray-500 text-sm mt-1">{{ selectedSpot.address }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Toggle Switch -->
                        <div class="flex items-center gap-3 pr-4 border-r border-gray-100">
                            <span class="text-sm font-bold"
                                :class="selectedSpot.is_active ? 'text-green-600' : 'text-gray-400'">
                                {{ selectedSpot.is_active ? 'Active' : 'Disabled' }}
                            </span>
                            <button @click.stop="toggleStatus(selectedSpot.id)"
                                class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors focus:outline-none"
                                :class="selectedSpot.is_active ? 'bg-[#1866ed]' : 'bg-gray-200'">
                                <span
                                    class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform shadow-sm"
                                    :class="selectedSpot.is_active ? 'translate-x-6' : 'translate-x-1'" />
                            </button>
                        </div>

                        <button @click="closeModal" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-6 md:p-8 space-y-10">
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Total Earnings</p>
                            <h4 class="text-3xl font-black text-gray-900">
                                CA${{ calculateTotalEarnings(selectedSpot.bookings) }}
                            </h4>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Upcoming Bookings</p>
                            <h4 class="text-3xl font-black text-gray-900">{{ upcomingBookings.length }}</h4>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Past Bookings</p>
                            <h4 class="text-3xl font-black text-gray-900">{{ pastBookings.length }}</h4>
                        </div>
                    </div>

                    <!-- Upcoming Bookings -->
                    <section>
                        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-green-500"></div>
                            Upcoming Bookings
                        </h3>

                        <div v-if="upcomingBookings.length === 0"
                            class="text-center py-10 bg-gray-50 rounded-[24px] border border-dashed border-gray-200">
                            <p class="text-gray-400 font-medium">No upcoming bookings yet</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="booking in upcomingBookings" :key="booking.id"
                                class="bg-white border border-gray-100 rounded-[24px] p-6 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-[#1866ed] font-bold">
                                            {{ booking.customer.charAt(0) }}
                                        </div>
                                        <div>
                                            <h5 class="font-bold text-gray-900">{{ booking.customer }}</h5>
                                            <p class="text-sm text-gray-500">{{ booking.email }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 flex-1 lg:max-w-2xl">
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Order ID</p>
                                            <p class="font-mono text-xs text-gray-600">#{{ booking.id }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Schedule</p>
                                            <p class="text-xs font-bold">{{ formatDate(booking.start_time) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Pricing</p>
                                            <p class="text-xs text-gray-600">
                                                Duration: {{ getDurationUnits(booking.start_time, booking.end_time) }} half-hours
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total</p>
                                            <p class="text-lg font-black text-gray-900">CA${{ booking.total_price }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Past Bookings -->
                    <section>
                        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2 text-gray-400">
                            <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                            Past Bookings
                        </h3>

                        <div v-if="pastBookings.length === 0"
                            class="text-center py-10 bg-gray-50 rounded-[24px] border border-dashed border-gray-200">
                            <p class="text-gray-400 font-medium">No past bookings</p>
                        </div>

                        <div v-else class="space-y-4 opacity-75">
                            <div v-for="booking in pastBookings" :key="booking.id"
                                class="bg-white border border-gray-100 rounded-[24px] p-6 shadow-sm">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 font-bold border border-gray-100">
                                            {{ booking.customer.charAt(0) }}
                                        </div>
                                        <div>
                                            <h5 class="font-bold text-gray-900">{{ booking.customer }}</h5>
                                            <p class="text-sm text-gray-500">{{ booking.email }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 flex-1 lg:max-w-2xl">
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Order ID</p>
                                            <p class="font-mono text-xs text-gray-600">#{{ booking.id }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Ended At</p>
                                            <p class="text-xs font-bold text-gray-400">{{ formatDate(booking.end_time) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Pricing</p>
                                            <p class="text-xs text-gray-600">Total Paid: CA${{ booking.total_price }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                                                Completed
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Danger Zone / Actions -->
                    <div class="pt-8 border-t border-gray-100 flex flex-col sm:flex-row gap-4">
                        <Link :href="route('spots.edit', selectedSpot.id)" 
                            class="flex-1 inline-flex items-center justify-center bg-white hover:bg-gray-50 text-gray-900 font-bold py-4 px-8 rounded-2xl border border-gray-200 transition shadow-sm">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Listing
                        </Link>
                        <button @click="deleteSpot(selectedSpot.id)" 
                            class="flex-1 inline-flex items-center justify-center bg-red-50 hover:bg-red-100 text-red-600 font-bold py-4 px-8 rounded-2xl border border-red-100 transition">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Listing
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-in {
    animation: animate-in 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes animate-in {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(10px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
</style>
