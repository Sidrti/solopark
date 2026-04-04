<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    spot: {
        type: Object,
        required: true
    }
});

const form = useForm({
    title: props.spot.title || '',
    address: props.spot.address || '',
    city: props.spot.city || '',
    state: props.spot.state || '',
    country: props.spot.country || '',
    latitude: props.spot.latitude || null,
    longitude: props.spot.longitude || null,
    type: props.spot.parking_type || null,
    price: props.spot.price_hourly || '',
    price_monthly: props.spot.price_monthly || '',
    selectedDays: props.spot.selectedDays || [],
    availFrom: props.spot.availFrom || '',
    availTo: props.spot.availTo || '',
    is24_7: !!props.spot.is_24_7,
    additionalPoints: props.spot.additional_points || [],
    features: props.spot.features || {
        cctv: false,
        evCharging: false,
        disabledAccess: false,
    },
    photos: [],
    removePhotos: [],
});

watch(() => form.is24_7, (is247) => {
    if (is247) {
        form.selectedDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        form.availFrom = '00:00';
        form.availTo = '23:59';
    }
});

const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
const newPoint = ref('');
const successSnack = ref(false);
const addressInput = ref(null);

onMounted(() => {
    const initAutocomplete = () => {
        const autocomplete = new window.google.maps.places.Autocomplete(addressInput.value, {
            types: ['geocode'],
        });

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            if (!place.geometry) return;

            form.address = place.formatted_address || place.name;
            form.latitude = place.geometry.location.lat();
            form.longitude = place.geometry.location.lng();

            form.city = '';
            form.state = '';
            form.country = '';

            if (place.address_components) {
                for (const component of place.address_components) {
                    const types = component.types;
                    if (types.includes('locality') || types.includes('postal_town')) {
                        form.city = component.long_name;
                    } else if (types.includes('administrative_area_level_1')) {
                        form.state = component.short_name;
                    } else if (types.includes('country')) {
                        form.country = component.long_name;
                    }
                }
            }
        });
    };

    if (window.google && window.google.maps && window.google.maps.places) {
        initAutocomplete();
    }
});

const toggleDay = (day) => {
    const index = form.selectedDays.indexOf(day);
    if (index === -1) {
        form.selectedDays.push(day);
    } else {
        form.selectedDays.splice(index, 1);
    }
};

const addPoint = (e) => {
    e?.preventDefault();
    if (newPoint.value.trim()) {
        form.additionalPoints.push(newPoint.value.trim());
        newPoint.value = '';
    }
};

const removePoint = (index) => {
    form.additionalPoints.splice(index, 1);
};

const handleFileUpload = (event) => {
    let files = [];
    for (let i = 0; i < event.target.files.length; i++) {
        files.push(event.target.files[i]);
    }
    form.photos = files;
};

const removeExistingPhoto = (id) => {
    if (!form.removePhotos.includes(id)) {
        form.removePhotos.push(id);
    }
};

const updateListing = () => {
    // Laravel bug with PATCH and multipart form data, using POST with _method=PATCH or just POST for update
    form.post(route('spots.update', props.spot.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            successSnack.value = true;
            setTimeout(() => {
                successSnack.value = false;
            }, 3000);
        },
    });
};
</script>

<template>
    <Head :title="'Edit ' + form.title" />

    <div class="min-h-screen bg-[#f8f9fa] font-sans text-gray-900 pb-20 relative">
        <Navbar :is-sticky="true" />

        <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <div class="mb-10">
                <Link :href="route('spots.my-listings')" class="inline-flex items-center text-sm font-bold text-gray-400 hover:text-[#1866ed] transition mb-6">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to My Listings
                </Link>
                <h1 class="text-3xl sm:text-[2.5rem] font-extrabold tracking-tight text-gray-900 leading-tight">
                    Edit Listing</h1>
            </div>

            <form @submit.prevent="updateListing" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-10">
                
                <div class="mb-4">
                    <h2 class="text-[19px] font-bold text-gray-900 mb-6">General Information</h2>

                    <div class="mb-6">
                        <label class="block text-[14px] font-bold text-gray-900 mb-2">Listing Title</label>
                        <input type="text" v-model="form.title" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[52px]" required />
                    </div>

                    <div class="mb-6">
                        <label class="block text-[14px] font-bold text-gray-900 mb-2">Spot Address</label>
                        <input type="text" ref="addressInput" v-model="form.address" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[52px]" required />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-2">
                        <div>
                            <label class="block text-[14px] font-bold text-gray-900 mb-2">Parking Type</label>
                            <select v-model="form.type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[52px]" required>
                                <option>Garage</option>
                                <option>Driveway</option>
                                <option>Uncovered Lot</option>
                                <option>Covered Lot</option>
                                <option>Backyard</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[14px] font-bold text-gray-900 mb-2">Price hourly (CA$)</label>
                            <input type="number" v-model="form.price" step="0.01" min="0" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[52px]" required />
                        </div>
                        <div>
                            <label class="block text-[14px] font-bold text-gray-900 mb-2">Price monthly (CA$)</label>
                            <input type="number" v-model="form.price_monthly" step="0.01" min="0" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[52px]" />
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200 my-10">

                <div class="mb-4">
                    <h2 class="text-[19px] font-bold text-gray-900 mb-6">Time Availability</h2>
                    <div class="mb-6">
                        <label class="block text-[14px] font-bold text-gray-900 mb-3">Available Days</label>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="day in days" :key="day" type="button" @click="toggleDay(day)" :class="[
                                form.selectedDays.includes(day) ? 'bg-[#1866ed] border-[#1866ed] text-white' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50',
                                'border rounded-full px-5 py-2 text-[14px] font-bold transition-colors'
                            ]">{{ day }}</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Available From</label>
                            <input type="time" v-model="form.availFrom" :disabled="form.is24_7" class="block w-full rounded-lg border-gray-300 h-[52px] disabled:bg-gray-100" />
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Available To</label>
                            <input type="time" v-model="form.availTo" :disabled="form.is24_7" class="block w-full rounded-lg border-gray-300 h-[52px] disabled:bg-gray-100" />
                        </div>
                    </div>
                    <div class="flex items-center">
                        <input id="24-7" type="checkbox" v-model="form.is24_7" class="h-5 w-5 text-[#1866ed] border-gray-300 rounded">
                        <label for="24-7" class="ml-2.5 block text-[15px] font-medium text-gray-900">Available 24/7</label>
                    </div>
                </div>

                <hr class="border-gray-200 my-10">

                <div>
                    <h2 class="text-[19px] font-bold text-gray-900 mb-6">Features & Rules</h2>
                    <div class="mb-6">
                        <div class="flex">
                            <input type="text" v-model="newPoint" @keydown.enter.prevent="addPoint" class="block w-full rounded-l-lg border-gray-300 h-[52px]" placeholder="Add a detail..." />
                            <button type="button" @click="addPoint" class="bg-[#1866ed] text-white px-6 font-bold rounded-r-lg">Add</button>
                        </div>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li v-for="(point, index) in form.additionalPoints" :key="index" class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200">
                            <span class="text-sm font-medium">{{ point }}</span>
                            <button type="button" @click="removePoint(index)" class="text-red-500 hover:text-red-700">Remove</button>
                        </li>
                    </ul>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.features.cctv" class="h-5 w-5 text-[#1866ed] rounded">
                            <span class="ml-2 text-sm font-medium text-gray-700">CCTV</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.features.evCharging" class="h-5 w-5 text-[#1866ed] rounded">
                            <span class="ml-2 text-sm font-medium text-gray-700">EV Charging</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.features.disabledAccess" class="h-5 w-5 text-[#1866ed] rounded">
                            <span class="ml-2 text-sm font-medium text-gray-700">Disabled Access</span>
                        </label>
                    </div>
                </div>

                <hr class="border-gray-200 my-10">

                <div class="mb-10">
                    <label class="block text-[14px] font-bold text-gray-900 mb-4">Photos</label>
                    
                    <!-- Existing Photos -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                        <div v-for="photo in spot.photos" :key="photo.id" class="relative group aspect-square rounded-xl overflow-hidden border border-gray-200" v-show="!form.removePhotos.includes(photo.id)">
                            <img :src="photo.url" class="w-full h-full object-cover" />
                            <button type="button" @click="removeExistingPhoto(photo.id)" class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:bg-gray-50 transition cursor-pointer relative">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            <p class="mt-1 text-sm text-[#1866ed] font-bold">Add more photos</p>
                            <input type="file" @change="handleFileUpload" class="absolute inset-0 opacity-0 cursor-pointer" multiple />
                        </div>
                    </div>
                </div>

                <div class="pt-4 space-y-4">
                    <button type="submit" :disabled="form.processing" class="w-full py-4 bg-[#1866ed] text-white rounded-[10px] text-lg font-bold hover:bg-blue-700 transition disabled:opacity-50">
                        {{ form.processing ? 'Updating...' : 'Update Listing' }}
                    </button>
                    <Link :href="route('spots.my-listings')" class="block w-full py-3 text-center font-bold text-gray-500 hover:text-gray-900 transition">Cancel</Link>
                </div>
            </form>
        </main>
    </div>
</template>
