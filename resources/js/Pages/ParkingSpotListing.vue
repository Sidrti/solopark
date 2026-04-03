<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import { GoogleMap, CustomMarker } from 'vue3-google-map';

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    locationStr: {
        type: String,
        default: 'Toronto, ON'
    },
    start: {
        type: String,
    },
    end: {
        type: String,
    },
    lat: {
        type: [String, Number]
    },
    lng: {
        type: [String, Number]
    },
    spots: {
        type: Array,
        default: () => []
    },
    type: {
        type: String,
        default: 'one-time'
    },
    startDate: {
        type: String,
    },
    endDate: {
        type: String,
    },
    startTime: {
        type: String,
    },
    endTime: {
        type: String,
    },
    days: {
        type: String,
    }
});

const pad = (num) => num.toString().padStart(2, '0');
const formatDt = (d) => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;

const location = ref(props.locationStr || 'Toronto, ON');
const searchLat = ref(props.lat);
const searchLng = ref(props.lng);

const searchType = computed(() => props.type || 'one-time');
const searchStartDate = computed(() => props.startDate || '');
const searchEndDate = computed(() => props.endDate || '');
const searchStartTime = computed(() => props.startTime || '09:00');
const searchEndTime = computed(() => props.endTime || '17:00');
const searchDays = computed(() => props.days || '');

const startTimeOneTime = ref(props.start || formatDt(new Date(new Date().getTime() + 60 * 60 * 1000)));
const endTimeOneTime = ref(props.end || formatDt(new Date(new Date().getTime() + 4 * 60 * 60 * 1000)));

const addressInput = ref(null);

onMounted(() => {
    const initAutocomplete = () => {
        if (!addressInput.value) return;
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

    if (window.google && window.google.maps && window.google.maps.places) {
        initAutocomplete();
    } else {
        // Fallback for async load
        setTimeout(initAutocomplete, 2000);
    }
});

const handleUpdateSearch = () => {
    const params = {
        location: location.value,
        lat: searchLat.value,
        lng: searchLng.value,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        type: searchType.value
    };

    if (searchType.value === 'one-time') {
        params.start = startTimeOneTime.value;
        params.end = endTimeOneTime.value;
    } else {
        params.startDate = searchStartDate.value;
        params.endDate = searchEndDate.value;
        params.startTime = searchStartTime.value;
        params.endTime = searchEndTime.value;
        params.days = searchDays.value;
    }

    router.get('/search', params);
};

const GOOGLE_MAPS_API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY || '';

// If lat/lng are passed, use them for the map center, otherwise default to Toronto
const mapLat = props.lat ? parseFloat(props.lat) : 43.6507;
const mapLng = props.lng ? parseFloat(props.lng) : -79.3830;
const mapCenter = { lat: mapLat, lng: mapLng };

// Use backend data if available, fallback to mock if completely empty
const parkingSpots = ref(props.spots);

const formatDateTimeShort = (dateStr) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ', ' +
        date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>

    <Head title="Search Results - Solopark" />

    <div
        class="h-screen flex flex-col bg-[#f1f3f5] font-sans text-gray-900 overflow-hidden selection:bg-[#1866ed] selection:text-white">
        <!-- Navigation Bar -->
        <Navbar :can-login="canLogin" :can-register="canRegister" :is-sticky="false"
            container-class="px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto md:max-w-none md:mx-0">
            <template #center>
                <!-- Compact Search (Visible md+) -->
                <div
                    class="hidden md:flex flex-1 max-w-4xl mx-8 items-center border border-gray-300 rounded-[12px] h-[54px] bg-white shadow-sm overflow-hidden hover:shadow transition">
                    
                    <div class="flex-1 px-4 py-2 border-r border-gray-200 flex items-center hover:bg-gray-50 h-full">
                        <svg class="w-[18px] h-[18px] text-gray-500 mr-2 shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input
                            ref="addressInput"
                            v-model="location"
                            type="text"
                            placeholder="Where are you going?"
                            class="bg-transparent border-0 focus:ring-0 text-[14px] font-medium text-gray-700 w-full p-0 placeholder-gray-400"
                        />
                    </div>
                    
                    <div v-if="props.type === 'one-time'" class="flex items-center">
                        <div class="px-4 py-2 border-r border-gray-200 flex flex-col justify-center hover:bg-gray-50 h-full min-w-[180px]">
                            <label class="text-[10px] font-bold text-gray-400 uppercase leading-none mb-1">Start</label>
                            <input 
                                v-model="startTimeOneTime"
                                type="datetime-local"
                                class="bg-transparent border-0 focus:ring-0 text-[13px] font-bold text-gray-700 p-0 leading-none"
                            />
                        </div>

                        <div class="px-4 py-2 border-r border-gray-200 flex flex-col justify-center hover:bg-gray-50 h-full min-w-[180px]">
                            <label class="text-[10px] font-bold text-gray-400 uppercase leading-none mb-1">End</label>
                            <input 
                                v-model="endTimeOneTime"
                                type="datetime-local"
                                class="bg-transparent border-0 focus:ring-0 text-[13px] font-bold text-gray-700 p-0 leading-none"
                            />
                        </div>
                    </div>

                    <div v-else-if="props.type === 'recurring'" class="flex flex-1 items-center">
                        <div class="px-4 py-2 border-gray-200 flex flex-col justify-center h-full min-w-[140px] border-r">
                            <label class="text-[10px] font-bold text-gray-400 uppercase leading-none mb-1">Dates</label>
                            <div class="text-[13px] font-bold text-gray-900 truncate max-w-[130px]">{{ props.startDate }} - {{ props.endDate }}</div>
                        </div>
                        <div class="px-4 py-2 border-gray-200 flex flex-col justify-center h-full min-w-[140px] border-r">
                            <label class="text-[10px] font-bold text-gray-400 uppercase leading-none mb-1">Time Range</label>
                            <div class="text-[13px] font-bold text-[#1866ed]">{{ props.startTime }} - {{ props.endTime }}</div>
                        </div>
                        <div class="px-4 py-2 border-gray-200 flex flex-col justify-center h-full min-w-[120px] border-r">
                            <label class="text-[10px] font-bold text-gray-400 uppercase leading-none mb-1">Weekly Days</label>
                            <div class="text-[13px] font-bold text-gray-900 truncate max-w-[100px]">{{ props.days }}</div>
                        </div>
                    </div>
                    <div v-else class="flex items-center">
                         <div class="px-6 py-2 border-r border-gray-200 flex flex-col justify-center h-full min-w-[200px]">
                            <label class="text-[10px] font-bold text-gray-400 uppercase leading-none mb-1">Monthly Range</label>
                            <div class="text-[13px] font-bold text-gray-900">{{ props.startDate }} to {{ props.endDate }}</div>
                        </div>
                    </div>

                    <button 
                        @click="handleUpdateSearch"
                        class="px-5 bg-[#1866ed] text-white h-full flex items-center justify-center hover:bg-blue-700 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </template>
        </Navbar>

        <!-- Search Layout -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <div class="flex-1 flex overflow-hidden">
                <!-- Results List -->
                <div
                    class="w-full md:w-5/12 lg:w-4/12 xl:w-[420px] h-full overflow-y-auto px-4 py-6 bg-[#f1f3f5] md:shadow-[inset_-10px_0_15px_-10px_rgba(0,0,0,0.05)] z-10 custom-scrollbar">
                    <!-- <div class="flex justify-between items-center mb-5 px-1">
                        <span class="text-[14px] font-bold text-gray-900">Sort by Relevance</span>
                        <svg class="w-[18px] h-[18px] text-gray-600 cursor-pointer" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </div> -->

                    <!-- Parking Cards -->
                    <div v-if="parkingSpots.length > 0">
                        <div v-for="(spot, i) in parkingSpots" :key="i"
                            class="bg-white rounded-[12px] mb-4 border border-gray-200 hover:shadow-[0_4px_12px_rgba(0,0,0,0.08)] hover:-translate-y-[2px] transition-all duration-200 relative overflow-hidden group cursor-pointer">
                            <div v-if="spot.badge"
                                class="absolute top-0 left-0 bg-[#1a1a1a] text-white text-[11px] font-bold px-3 py-1 rounded-tl-[12px] rounded-br-[12px] z-10">
                                {{ spot.badge }}
                            </div>
                            <div class="flex h-[140px]">
                                <div class="w-[120px] shrink-0 h-full overflow-hidden">
                                    <img :src="spot.image"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        alt="Parking spot" />
                                </div>
                                <div class="flex-1 p-3 flex flex-col justify-between relative">
                                    <div class="flex justify-between items-start">
                                        <div class="pr-2">
                                            <div
                                                class="text-[14px] font-bold text-gray-900 leading-tight line-clamp-2 max-w-[160px]">
                                                {{ spot.address }}</div>

                                            <div class="text-[12px] text-gray-500 mt-1 flex items-center">
                                                <svg class="w-[14px] h-[14px] mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M13.73 21a2 2 0 01-3.46 0m2-18v3m-7.88 2.37l2.6 1.5m10.56-1.5l-2.6 1.5M4.93 17.65l2.6-1.5m10.56 1.5l-2.6-1.5M12 11v3">
                                                    </path>
                                                </svg>
                                                {{ spot.walk }} <span class="mx-1">•</span> {{ spot.dist }} mi
                                            </div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="text-[18px] font-extrabold text-[#1a1a1a]">CA${{ spot.price }}</div>
                                            <div class="text-[12px] text-[#1866ed] font-medium underline mt-0.5">Total</div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end mt-2 items-center">
                                        <Link :href="route('spot-details', { id: spot.id, type: props.type, start: props.start, end: props.end, startDate: props.startDate, endDate: props.endDate, startTime: props.startTime, endTime: props.endTime, days: props.days, lat: props.lat, lng: props.lng })" class="text-[14px] font-bold text-[#1866ed] mr-5 hover:text-blue-800 transition-colors">Details</Link>
                                        <Link :href="route('spot-book', { id: spot.id, type: props.type, start: props.start, end: props.end, startDate: props.startDate, endDate: props.endDate, startTime: props.startTime, endTime: props.endTime, days: props.days, lat: props.lat, lng: props.lng })" class="bg-[#1866ed] hover:bg-blue-700 text-white text-[14px] font-bold py-2 px-6 rounded-[8px] transition-colors">Book Now</Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="flex flex-col items-center justify-center py-20 px-4 text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No spots found</h3>
                        <p class="text-gray-500 max-w-[280px] mb-8 text-[14px] leading-relaxed">We couldn't find any parking spots matching your search criteria. Try adjusting your dates or searching a nearby area.</p>
                        <button @click="location = ''; startTime = ''; endTime = ''; handleUpdateSearch()" class="inline-flex items-center text-[15px] font-bold text-[#1866ed] hover:text-blue-800 transition">
                            Reset Search
                        </button>
                    </div>
                </div>

                <!-- Map Section -->
                <div class="hidden md:block md:w-7/12 lg:w-8/12 xl:flex-1 h-full relative bg-[#e5e7eb] overflow-hidden">
                    <GoogleMap :api-key="GOOGLE_MAPS_API_KEY" style="width: 100%; height: 100%" :center="mapCenter"
                        :zoom="15" :disableDefaultUI="true">
                        <!-- Destination marker (e.g. at mapCenter) -->
                        <CustomMarker :options="{ position: mapCenter }">
                            <div class="transform -translate-x-1/2 -translate-y-1/2">
                                <svg class="w-[32px] h-[32px] text-red-500 drop-shadow-lg" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z">
                                    </path>
                                </svg>
                            </div>
                        </CustomMarker>

                        <!-- Pins -->
                        <CustomMarker v-for="(spot, i) in parkingSpots" :key="'pin' + i"
                            :options="{ position: { lat: spot.lat, lng: spot.lng } }">
                            <div
                                class="bg-white border border-gray-300 rounded-[20px] px-2.5 py-1 font-bold text-[13px] text-gray-900 shadow-[0_2px_6px_rgba(0,0,0,0.2)] cursor-pointer hover:bg-[#1866ed] hover:text-white hover:z-50 hover:border-[#1866ed] transition-all duration-200 transform hover:scale-110">
                                ${{ Math.floor(spot.price) }}
                            </div>
                        </CustomMarker>
                    </GoogleMap>

                    <!-- Custom Map Controls overlaid on top of Google Map -->
                    <div class="absolute top-5 right-5 flex flex-col space-y-2 pointer-events-none">
                        <button
                            class="bg-white p-2 rounded-lg shadow-[0_2px_8px_rgba(0,0,0,0.1)] hover:bg-gray-50 text-gray-700 transition w-9 h-9 flex items-center justify-center pointer-events-auto">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                        </button>
                        <button
                            class="bg-white p-2 rounded-lg shadow-[0_2px_8px_rgba(0,0,0,0.1)] hover:bg-gray-50 text-gray-700 transition w-9 h-9 flex items-center justify-center pointer-events-auto">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 10px;
}
</style>
