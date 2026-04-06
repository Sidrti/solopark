<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const location = ref('');
const searchLat = ref(null);
const searchLng = ref(null);
const startTime = ref('');
const endTime = ref('');
const addressInput = ref(null);

onMounted(() => {
    const initAutocomplete = () => {
        const autocomplete = new window.google.maps.places.Autocomplete(addressInput.value, {
            types: ['geocode'],
        });

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            location.value = place.formatted_address || place.name;
            if (place.geometry) {
                searchLat.value = place.geometry.location.lat();
                searchLng.value = place.geometry.location.lng();
            }
        });
    };

    const getCurrentLocation = () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    searchLat.value = lat;
                    searchLng.value = lng;

                    // Reverse geocode to get address if google maps is loaded
                    if (window.google && window.google.maps) {
                        const geocoder = new window.google.maps.Geocoder();
                        geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                            if (status === 'OK' && results[0]) {
                                location.value = results[0].formatted_address;
                            }
                        });
                    }
                },
                (error) => {
                    console.log('User denied geolocation or error occurred');
                }
            );
        }
    };

    if (window.google && window.google.maps && window.google.maps.places) {
        initAutocomplete();
        if (!location.value) getCurrentLocation();
    } else if (!document.querySelector('script[src*="maps.googleapis.com"]')) {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${import.meta.env.VITE_GOOGLE_MAPS_API_KEY || ''}&libraries=places`;
        script.onload = () => {
            initAutocomplete();
            if (!location.value) getCurrentLocation();
        };
        document.head.appendChild(script);
    } else {
        // Wait for it to load
        setTimeout(() => {
            initAutocomplete();
            if (!location.value) getCurrentLocation();
        }, 1000);
    }
});

// Initialize times
const initTimes = () => {
    const now = new Date();
    now.setMinutes(0, 0, 0);
    now.setHours(now.getHours() + 1);

    const pad = (num) => num.toString().padStart(2, '0');

    // Format YYYY-MM-DDTHH:mm
    const formatDt = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;

    startTime.value = formatDt(now);

    const later = new Date(now.getTime() + 3 * 60 * 60 * 1000);
    endTime.value = formatDt(later);
};

initTimes();

const activeTab = ref('one-time');
const recurringDays = ref([]);
const today = new Date().toISOString().split('T')[0];
const startDate = ref(today);
const endDate = ref(new Date(new Date().getTime() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]);
const recurringStartTime = ref('09:00');
const recurringEndTime = ref('17:00');

const monthlyStartDate = ref(today);
const monthlyEndDate = ref(new Date(new Date().getTime() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]);

watch(monthlyStartDate, (newVal) => {
    if (activeTab.value === 'monthly') {
        const d = new Date(newVal);
        d.setDate(d.getDate() + 30);
        monthlyEndDate.value = d.toISOString().split('T')[0];
    }
});

const adjustMonthlyEndDate = (direction) => {
    const start = new Date(monthlyStartDate.value);
    const currentEnd = new Date(monthlyEndDate.value);
    const diffDays = Math.round((currentEnd - start) / (24 * 60 * 60 * 1000));
    let newDiff = diffDays;

    if (direction === 'up') {
        newDiff += 30;
    } else {
        newDiff = Math.max(30, newDiff - 30);
    }

    const newEnd = new Date(start);
    newEnd.setDate(newEnd.getDate() + newDiff);
    monthlyEndDate.value = newEnd.toISOString().split('T')[0];
};

const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

const toggleDay = (day) => {
    if (recurringDays.value.includes(day)) {
        recurringDays.value = recurringDays.value.filter(d => d !== day);
    } else {
        recurringDays.value.push(day);
    }
};

const searchButtonText = computed(() => {
    if (activeTab.value === 'one-time') return 'Find Parking Spots';
    if (activeTab.value === 'recurring') return 'Find Recurring Spots';
    return 'Find Monthly Spots';
});

const handleSearch = () => {
    const params = {
        location: location.value,
        lat: searchLat.value,
        lng: searchLng.value,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        type: activeTab.value
    };

    if (activeTab.value === 'one-time') {
        params.start = startTime.value;
        params.end = endTime.value;
    } else if (activeTab.value === 'recurring') {
        params.startDate = startDate.value;
        params.endDate = endDate.value;
        params.startTime = recurringStartTime.value;
        params.endTime = recurringEndTime.value;
        params.days = recurringDays.value.join(',');
    } else {
        // Monthly
        params.startDate = monthlyStartDate.value;
        params.endDate = monthlyEndDate.value;
    }

    router.get('/search', params);
};
</script>

<template>

    <Head title="Find and reserve parking in Canada" />

    <div class="min-h-screen bg-white font-sans text-gray-900 selection:bg-[#1866ed] selection:text-white">
        <!-- Navigation Bar -->
        <Navbar :can-login="canLogin" :can-register="canRegister" :is-sticky="true" />

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-20 items-center">

                <!-- Left Column: Search Section -->
                <div>
                    <!-- Breadcrumb -->
                    <!-- <div class="text-[13px] text-[#1866ed] font-medium flex items-center mb-6">
                        Cities 
                        <svg class="w-4 h-4 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg> 
                        <span class="text-gray-600">Toronto, ON</span>
                    </div> -->

                    <h1 class="text-4xl sm:text-[3rem] font-bold leading-[1.1] text-gray-900 mb-8 tracking-tight">
                        Find and reserve parking in Canada
                    </h1>

                    <!-- Search Form -->
                    <div class="mb-6 flex space-x-1 bg-gray-100 p-1 rounded-xl">
                        <button type="button" @click="activeTab = 'one-time'" :class="[
                            'flex-1 py-2 text-[14px] font-bold rounded-lg transition-all',
                            activeTab === 'one-time' ? 'bg-white text-[#1866ed] shadow-sm' : 'text-gray-500 hover:text-gray-700'
                        ]">
                            One Time
                        </button>
                        <button type="button" @click="activeTab = 'recurring'" :class="[
                            'flex-1 py-2 text-[14px] font-bold rounded-lg transition-all',
                            activeTab === 'recurring' ? 'bg-white text-[#1866ed] shadow-sm' : 'text-gray-500 hover:text-gray-700'
                        ]">
                            Recurring
                        </button>
                        <button type="button" @click="activeTab = 'monthly'" :class="[
                            'flex-1 py-2 text-[14px] font-bold rounded-lg transition-all',
                            activeTab === 'monthly' ? 'bg-white text-[#1866ed] shadow-sm' : 'text-gray-500 hover:text-gray-700'
                        ]">
                            Monthly
                        </button>
                    </div>

                    <form @submit.prevent="handleSearch" class="space-y-4">

                        <!-- Location Input -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input id="location" ref="addressInput" type="text"
                                class="pl-10 block w-full border-gray-300 rounded-lg py-3 focus:ring-[#1866ed] focus:border-[#1866ed] placeholder-gray-500 shadow-sm"
                                v-model="location" placeholder="Where are you going?" />
                        </div>

                        <!-- One-Time Search Fields -->
                        <div v-if="activeTab === 'one-time'"
                            class="grid grid-cols-2 border border-gray-300 rounded-lg overflow-hidden relative bg-white shadow-sm">
                            <div
                                class="p-3 border-r border-gray-300 hover:bg-gray-50 transition cursor-pointer relative group">
                                <label for="start-time"
                                    class="block text-xs font-semibold text-gray-500 mb-1 flex items-center cursor-pointer">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    Start time
                                </label>
                                <input type="datetime-local" id="start-time" v-model="startTime"
                                    class="block w-full text-sm font-medium text-gray-900 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer" />
                            </div>

                            <div class="p-3 hover:bg-gray-50 transition cursor-pointer relative group">
                                <label for="end-time"
                                    class="block text-xs font-semibold text-gray-500 mb-1 flex items-center cursor-pointer">
                                    End time
                                </label>
                                <div class="flex items-center justify-between">
                                    <input type="datetime-local" id="end-time" v-model="endTime"
                                        class="block w-full text-sm font-medium text-gray-900 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer" />
                                </div>
                            </div>
                        </div>

                        <!-- Recurring Search Fields -->
                        <div v-else-if="activeTab === 'recurring'" class="space-y-4">
                            <!-- ... existing recurring fields ... -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-3 border border-gray-300 rounded-lg bg-white shadow-sm">
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Start Date</label>
                                    <input type="date" v-model="startDate"
                                        class="block w-full text-sm font-medium border-0 p-0 focus:ring-0" />
                                </div>
                                <div class="p-3 border border-gray-300 rounded-lg bg-white shadow-sm">
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">End Date</label>
                                    <input type="date" v-model="endDate"
                                        class="block w-full text-sm font-medium border-0 p-0 focus:ring-0" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-3 border border-gray-300 rounded-lg bg-white shadow-sm">
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Daily Start
                                        Time</label>
                                    <input type="time" v-model="recurringStartTime"
                                        class="block w-full text-sm font-medium border-0 p-0 focus:ring-0" />
                                </div>
                                <div class="p-3 border border-gray-300 rounded-lg bg-white shadow-sm">
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Daily End Time</label>
                                    <input type="time" v-model="recurringEndTime"
                                        class="block w-full text-sm font-medium border-0 p-0 focus:ring-0" />
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button v-for="day in days" :key="day" type="button" @click="toggleDay(day)" :class="[
                                    'px-3 py-1.5 rounded-full text-xs font-bold transition-all border',
                                    recurringDays.includes(day)
                                        ? 'bg-[#1866ed] text-white border-[#1866ed]'
                                        : 'bg-white text-gray-500 border-gray-200 hover:border-gray-300'
                                ]">
                                    {{ day }}
                                </button>
                            </div>
                        </div>

                        <!-- Monthly Search Fields -->
                        <div v-else class="space-y-4">
                            <div
                                class="grid grid-cols-1 border border-gray-300 rounded-lg overflow-hidden bg-white shadow-sm divide-y divide-gray-200">
                                <div class="p-3 hover:bg-gray-50 transition">
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Select Start
                                        Date</label>
                                    <input type="date" v-model="monthlyStartDate"
                                        class="block w-full text-sm font-medium border-0 p-0 focus:ring-0 cursor-pointer" />
                                </div>
                                <div class="p-3 bg-blue-50/30 flex items-center justify-between">
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-[#1866ed] mb-1 uppercase tracking-wider">Duration
                                            (Multiples of 30 days)</label>
                                        <div class="text-[15px] font-bold text-gray-900">
                                            Ending on: {{ new Date(monthlyEndDate).toLocaleDateString('en-US', {
                                                month:
                                                    'short', day: 'numeric', year: 'numeric'
                                            }) }}
                                            <span class="text-xs font-medium text-gray-500 ml-2">({{ Math.round((new
                                                Date(monthlyEndDate) - new Date(monthlyStartDate)) / (24 * 60 * 60 *
                                                    1000)) }} days)</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" @click="adjustMonthlyEndDate('down')"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-gray-200 text-gray-600 hover:border-[#1866ed] hover:text-[#1866ed] transition shadow-sm">-</button>
                                        <button type="button" @click="adjustMonthlyEndDate('up')"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-gray-200 text-gray-600 hover:border-[#1866ed] hover:text-[#1866ed] transition shadow-sm">+</button>
                                    </div>
                                </div>
                            </div>
                            <p class="text-[11px] text-gray-500 italic px-1">Monthly bookings are set in 30-day blocks
                                for best rates.</p>
                        </div>

                        <!-- Find Button -->
                        <div class="pt-2">
                            <PrimaryButton
                                class="w-full text-center flex justify-center py-4 rounded-lg bg-[#1866ed] hover:bg-blue-700 active:bg-blue-800 text-base font-bold shadow-none border border-transparent !px-4">
                                {{ searchButtonText }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Hero Image -->
                <div
                    class="mt-8 md:mt-0 relative h-64 sm:h-80 md:h-full md:min-h-[500px] w-full rounded-[24px] overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1517090504586-fde19ea6066f?q=80&w=2070&auto=format&fit=crop"
                        alt="Toronto Skyline" class="absolute inset-0 w-full h-full object-cover">
                </div>

            </div>
        </main>

        <!-- =========================================================
       HOW IT WORKS
  ========================================================= -->
        <section class="bg-[#f5f8ff] py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-14">
                    <span class="inline-block text-xs font-bold uppercase tracking-widest text-[#1866ed] mb-3">Simple
                        process</span>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Park in 3 easy steps</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                    <!-- Connector line (desktop only) -->
                    <div class="hidden md:block absolute top-10 left-1/6 right-1/6 h-px bg-[#1866ed] opacity-20"
                        style="left:16.6%;right:16.6%"></div>

                    <!-- Step 1 -->
                    <div
                        class="relative bg-white rounded-2xl p-8 shadow-sm border border-gray-100 flex flex-col items-start">
                        <div
                            class="w-12 h-12 rounded-xl bg-[#1866ed] flex items-center justify-center mb-6 text-white font-bold text-lg shadow">
                            1</div>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">Search your destination</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Enter your destination and desired parking
                            times. We'll show you every available spot nearby in real time.</p>
                    </div>

                    <!-- Step 2 -->
                    <div
                        class="relative bg-white rounded-2xl p-8 shadow-sm border border-gray-100 flex flex-col items-start">
                        <div
                            class="w-12 h-12 rounded-xl bg-[#1866ed] flex items-center justify-center mb-6 text-white font-bold text-lg shadow">
                            2</div>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">Compare & choose</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Browse spots on a live map, compare prices and
                            amenities, then pick the one that suits you best.</p>
                    </div>

                    <!-- Step 3 -->
                    <div
                        class="relative bg-white rounded-2xl p-8 shadow-sm border border-gray-100 flex flex-col items-start">
                        <div
                            class="w-12 h-12 rounded-xl bg-[#1866ed] flex items-center justify-center mb-6 text-white font-bold text-lg shadow">
                            3</div>
                        <h3 class="font-bold text-lg text-gray-900 mb-2">Reserve & Park</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Pay securely online, receive your booking
                            confirmation instantly, and arrive stress‑free — your spot is waiting.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================================
       WHY CHOOSE US  (features / value props)
  ========================================================= -->
        <section class="bg-white py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

                    <!-- Left: feature list -->
                    <div>
                        <span class="inline-block text-xs font-bold uppercase tracking-widest text-[#1866ed] mb-3">Why
                            SoloPark</span>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight mb-10">Everything you
                            need, nothing you don't</h2>

                        <div class="space-y-7">
                            <div class="flex gap-5 items-start">
                                <div
                                    class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#eef3fd] flex items-center justify-center">
                                    <!-- Lock icon -->
                                    <svg class="w-5 h-5 text-[#1866ed]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Guaranteed reservation</h4>
                                    <p class="text-gray-500 text-sm leading-relaxed">Your spot is locked the moment you
                                        book — no more circling the block or arriving to find it taken.</p>
                                </div>
                            </div>

                            <div class="flex gap-5 items-start">
                                <div
                                    class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#eef3fd] flex items-center justify-center">
                                    <!-- Dollar icon -->
                                    <svg class="w-5 h-5 text-[#1866ed]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Transparent pricing</h4>
                                    <p class="text-gray-500 text-sm leading-relaxed">See the full price upfront — no
                                        surprise fees at the gate. What you see is exactly what you pay.</p>
                                </div>
                            </div>

                            <div class="flex gap-5 items-start">
                                <div
                                    class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#eef3fd] flex items-center justify-center">
                                    <!-- Clock icon -->
                                    <svg class="w-5 h-5 text-[#1866ed]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Flexible duration</h4>
                                    <p class="text-gray-500 text-sm leading-relaxed">Book by the hour or for the whole
                                        day. Extend your session on the go right from your phone.</p>
                                </div>
                            </div>

                            <div class="flex gap-5 items-start">
                                <div
                                    class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#eef3fd] flex items-center justify-center">
                                    <!-- Shield icon -->
                                    <svg class="w-5 h-5 text-[#1866ed]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Secure payments</h4>
                                    <p class="text-gray-500 text-sm leading-relaxed">All transactions are encrypted and
                                        PCI-compliant. Pay with any major card or digital wallet.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: stat cards -->
                    <div class="grid grid-cols-2 gap-5">
                        <div
                            class="bg-[#1866ed] rounded-2xl p-7 text-white flex flex-col justify-between min-h-[160px]">
                            <span class="text-4xl font-bold leading-none">500+</span>
                            <span class="text-sm font-medium opacity-80 mt-3">Parking locations across Canada</span>
                        </div>
                        <div
                            class="bg-[#f5f8ff] rounded-2xl p-7 flex flex-col justify-between min-h-[160px] border border-gray-100">
                            <span class="text-4xl font-bold leading-none text-gray-900">50k+</span>
                            <span class="text-sm font-medium text-gray-500 mt-3">Happy drivers parked so far</span>
                        </div>
                        <div
                            class="bg-[#f5f8ff] rounded-2xl p-7 flex flex-col justify-between min-h-[160px] border border-gray-100">
                            <span class="text-4xl font-bold leading-none text-gray-900">4.9★</span>
                            <span class="text-sm font-medium text-gray-500 mt-3">Average rating from our users</span>
                        </div>
                        <div class="bg-gray-900 rounded-2xl p-7 text-white flex flex-col justify-between min-h-[160px]">
                            <span class="text-4xl font-bold leading-none">24/7</span>
                            <span class="text-sm font-medium opacity-70 mt-3">Customer support, always on</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- =========================================================

       TESTIMONIALS
  ========================================================= -->
        <!-- <section class="bg-white py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-14">
                    <span
                        class="inline-block text-xs font-bold uppercase tracking-widest text-[#1866ed] mb-3">Reviews</span>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Drivers love SoloPark</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Review 1 -->
        <!-- <div class="bg-[#f5f8ff] rounded-2xl p-7 border border-gray-100">
            <div class="flex items-center gap-1 mb-4">
                <svg v-for="i in 5" :key="i" class="w-4 h-4 text-[#1866ed]" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            </div>
            <p class="text-gray-700 text-sm leading-relaxed mb-5">"I used to waste 20 minutes finding
                parking near the ACC before every game. Now I book the night before and walk straight to my
                spot. Total game-changer."</p>
            <div class="flex items-center gap-3">
                <div
                    class="w-9 h-9 rounded-full bg-[#1866ed] flex items-center justify-center text-white text-sm font-bold">
                    JK</div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">James K.</p>
                    <p class="text-xs text-gray-400">Leafs season ticket holder</p>
                </div>
            </div>
        </div>

        <div class="bg-[#1866ed] rounded-2xl p-7">
            <div class="flex items-center gap-1 mb-4">
                <svg v-for="i in 5" :key="i" class="w-4 h-4 text-white opacity-90" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            </div>
            <p class="text-white/90 text-sm leading-relaxed mb-5">"The transparent pricing is what sold me.
                No hidden fees, no last-minute price jumps. I booked a spot near Union Station for my
                commute every day this month."</p>
            <div class="flex items-center gap-3">
                <div
                    class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-white text-sm font-bold">
                    SM</div>
                <div>
                    <p class="text-sm font-semibold text-white">Sara M.</p>
                    <p class="text-xs text-white/60">Daily commuter</p>
                </div>
            </div>
        </div> -->

        <!-- Review 3 -->
        <!-- <div class="bg-[#f5f8ff] rounded-2xl p-7 border border-gray-100">
            <div class="flex items-center gap-1 mb-4">
                <svg v-for="i in 5" :key="i" class="w-4 h-4 text-[#1866ed]" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            </div>
            <p class="text-gray-700 text-sm leading-relaxed mb-5">"Visited Toronto for a conference and had
                zero parking stress. Booked from my hotel room the evening before, arrived, drove in.
                Couldn't have been smoother."</p>
            <div class="flex items-center gap-3">
                <div
                    class="w-9 h-9 rounded-full bg-[#1866ed] flex items-center justify-center text-white text-sm font-bold">
                    RT</div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Raj T.</p>
                    <p class="text-xs text-gray-400">Visitor from Ottawa</p>
                </div>
            </div>
        </div> -->
        <!-- </div>
    </div>
    </section>  -->

        <!-- =========================================================
       LIST YOUR SPOT CTA  (for parking space owners)
  ========================================================= -->
        <section class="bg-gray-900 py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="inline-block text-xs font-bold uppercase tracking-widest text-[#5b9bff] mb-3">For
                            space owners</span>
                        <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-5">Own a parking
                            spot?<br>Start earning today.</h2>
                        <p class="text-gray-400 leading-relaxed mb-8 max-w-md">List your driveway, garage, or lot on
                            SoloPark and earn passive income whenever it's available. We handle bookings, payments, and
                            support — you just collect the money.</p>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="/list-spot"
                                class="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-[#1866ed] hover:bg-blue-600 text-white font-bold text-sm transition">
                                List your space
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>

                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 flex flex-col gap-2">
                            <span class="text-3xl font-bold text-white">Book ahead</span>
                            <span class="text-gray-400 text-sm">Reserve before you arrive</span>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 flex flex-col gap-2">
                            <span class="text-3xl font-bold text-white">Know the cost</span>
                            <span class="text-gray-400 text-sm">See pricing upfront</span>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 flex flex-col gap-2">
                            <span class="text-3xl font-bold text-white">Choose your stay </span>
                            <span class="text-gray-400 text-sm">Hourly, daily, or monthly</span>
                        </div>
                        <div class="bg-[#1866ed]/30 border border-[#1866ed]/40 rounded-2xl p-6 flex flex-col gap-2">
                            <span class="text-3xl font-bold text-white">Pay Securly</span>
                            <span class="text-gray-400 text-sm">Simple and secure checkout</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================================
       FAQ
  ========================================================= -->
        <section class="bg-white py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-14">
                    <span
                        class="inline-block text-xs font-bold uppercase tracking-widest text-[#1866ed] mb-3">FAQ</span>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Common questions</h2>
                </div>

                <div class="space-y-4" x-data="{ open: null }">
                    <!-- Item -->
                    <details class="group bg-[#f5f8ff] rounded-xl border border-gray-100 overflow-hidden">
                        <summary
                            class="flex items-center justify-between px-6 py-5 cursor-pointer list-none font-semibold text-gray-900 text-sm">
                            Can I cancel or modify my booking?
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="px-6 pb-5 text-gray-500 text-sm leading-relaxed">Yes. You can cancel or modify your
                            reservation up to 1 hour before your booking start time at no charge. Cancellations made
                            within 1 hour of start time may be subject to a partial fee depending on the lot's policy.
                        </p>
                    </details>

                    <details class="group bg-[#f5f8ff] rounded-xl border border-gray-100 overflow-hidden">
                        <summary
                            class="flex items-center justify-between px-6 py-5 cursor-pointer list-none font-semibold text-gray-900 text-sm">
                            How do I access my reserved spot?
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="px-6 pb-5 text-gray-500 text-sm leading-relaxed">After booking you'll receive a
                            confirmation email with your spot number, access instructions (gate code, QR code, or
                            attendant check-in), and directions. All the info you need is also available in your account
                            dashboard.</p>
                    </details>

                    <details class="group bg-[#f5f8ff] rounded-xl border border-gray-100 overflow-hidden">
                        <summary
                            class="flex items-center justify-between px-6 py-5 cursor-pointer list-none font-semibold text-gray-900 text-sm">
                            What payment methods do you accept?
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="px-6 pb-5 text-gray-500 text-sm leading-relaxed">We accept all major credit and debit
                            cards (Visa, Mastercard, Amex), as well as Apple Pay and Google Pay. All payments are
                            processed securely through Stripe.</p>
                    </details>

                    <details class="group bg-[#f5f8ff] rounded-xl border border-gray-100 overflow-hidden">
                        <summary
                            class="flex items-center justify-between px-6 py-5 cursor-pointer list-none font-semibold text-gray-900 text-sm">
                            Is there a mobile app?
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="px-6 pb-5 text-gray-500 text-sm leading-relaxed">Our mobile-optimised website works
                            great on any device. A dedicated iOS and Android app is coming soon — sign up to be notified
                            when it launches.</p>
                    </details>

                    <details class="group bg-[#f5f8ff] rounded-xl border border-gray-100 overflow-hidden">
                        <summary
                            class="flex items-center justify-between px-6 py-5 cursor-pointer list-none font-semibold text-gray-900 text-sm">
                            What if the lot is full when I arrive?
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="px-6 pb-5 text-gray-500 text-sm leading-relaxed">This is rare since your spot is
                            reserved in advance, but if it does happen we'll find you an equivalent alternative nearby
                            and cover any price difference. Your booking is guaranteed.</p>
                    </details>
                </div>
            </div>
        </section>

        <!-- =========================================================
       FOOTER CTA BANNER
  ========================================================= -->
        <!-- <section class="bg-[#1866ed] py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-4">Ready to park smarter?</h2>
                <p class="text-white/80 mb-8 text-lg">Join thousands of Toronto drivers who've ditched the parking
                    stress for good.</p>
                <a href="#top"
                    class="inline-flex items-center px-8 py-4 rounded-xl bg-white text-[#1866ed] font-bold text-base hover:bg-blue-50 transition shadow-lg">
                    Find parking now
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </section> -->

        <!-- =========================================================
       FOOTER
  ========================================================= -->
        <Footer />
    </div>
</template>
