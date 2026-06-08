<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import Navbar from '@/Components/Navbar.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const form = useForm({
    title: '',
    address: '',
    city: '',
    state: '',
    country: '',
    latitude: null,
    longitude: null,
    type: null,
    price: '',
    price_monthly: '',
    selectedDays: [],
    availFrom: '',
    availTo: '',
    is24_7: false,
    additionalPoints: [],
    features: {
        cctv: false,
        evCharging: false,
        disabledAccess: false,
    },
    photos: [],
    contact_number: '',
});

watch(() => form.is24_7, (is247) => {
    if (is247) {
        form.selectedDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        form.availFrom = '00:00';
        form.availTo = '23:59';
    } else {
        form.availFrom = '';
        form.availTo = '';
        form.selectedDays = [];
    }
});

// Expose form errors if any in the template using form.errors

const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
const newPoint = ref('');
const successSnack = ref(false);
const showSuccessModal = ref(false);
const addressInput = ref(null);

const formatMobile = () => {
    const cleaned = form.contact_number.replace(/\D/g, '').substring(0, 10);
    const match = cleaned.match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
    if (!match) {
        form.contact_number = '';
    } else {
        form.contact_number = !match[2] ? match[1] : '(' + match[1] + ') ' + match[2] + (match[3] ? '-' + match[3] : '');
    }
};

const goToMyListings = () => {
    showSuccessModal.value = false;
    router.visit(route('spots.my-listings'));
};

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

            // Reset address components
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
    } else if (!document.querySelector('script[src*="maps.googleapis.com"]')) {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${import.meta.env.VITE_GOOGLE_MAPS_API_KEY || ''}&libraries=places`;
        script.onload = initAutocomplete;
        document.head.appendChild(script);
    } else {
        // Wait for it to load
        setTimeout(initAutocomplete, 1000);
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
    // Collect raw raw file instances
    let files = [];
    for (let i = 0; i < event.target.files.length; i++) {
        files.push(event.target.files[i]);
    }
    form.photos = files;
};

const submitListing = () => {
    form.post('/list-spot', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showSuccessModal.value = true;
        },
        onError: (errors) => {
            console.error('Validation Errors:', errors);
            if (Object.keys(errors).length > 0) {
                alert('Form submission failed due to validation errors. Check the console or fields.');
            }
        }
    });
};
</script>

<template>

    <Head title="List your parking spot - Solopark" />

    <div
        class="min-h-screen bg-[#f8f9fa] font-sans text-gray-900 selection:bg-[#1866ed] selection:text-white pb-20 relative">
        <Navbar :can-login="canLogin" :can-register="canRegister" :is-sticky="true" />

        <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <div class="text-center mb-10">
                <h1 class="text-3xl sm:text-[2.5rem] font-extrabold tracking-tight text-gray-900 mb-4 leading-tight">
                    List your parking spot</h1>
                <p class="text-[17px] text-gray-500 max-w-xl mx-auto">Earn money by sharing your unused space with our
                    community.</p>
            </div>

            <form @submit.prevent="submitListing"
                class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-10">

                <!-- General Information -->
                <div class="mb-4">
                    <h2 class="text-[19px] font-bold text-gray-900 mb-6">General Information</h2>

                    <div class="mb-6">
                        <label class="block text-[14px] font-bold text-gray-900 mb-2">Listing Title</label>
                        <input type="text" v-model="form.title"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[52px]"
                            placeholder="e.g. Spacious Driveway Near Downtown" required />
                    </div>

                    <div class="mb-6">
                        <label class="block text-[14px] font-bold text-gray-900 mb-2">Spot Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <input type="text" ref="addressInput" v-model="form.address"
                                class="pl-10 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[52px]"
                                placeholder="Enter the address of your parking spot" required />
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-[14px] font-bold text-gray-900 mb-2">Host Phone Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-[15px]">+1</span>
                                <div class="h-5 w-px bg-gray-300 mx-2"></div>
                            </div>
                            <input type="tel" v-model="form.contact_number" @input="formatMobile" :class="[
                                'pl-[52px] block w-full rounded-lg shadow-sm sm:text-[15px] h-[52px] placeholder-gray-400',
                                form.errors.contact_number ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#1866ed] focus:ring-[#1866ed]'
                            ]" placeholder="(555) 000-0000" required />
                        </div>
                        <p v-if="form.errors.contact_number" class="mt-2 text-sm text-red-600 font-medium">{{ form.errors.contact_number }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-2">
                        <div>
                            <label class="block text-[14px] font-bold text-gray-900 mb-2">Parking Type</label>
                            <select v-model="form.type"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[52px]"
                                required>
                                <option value="" disabled selected hidden>Select type</option>
                                <option>Garage</option>
                                <option>Driveway</option>
                                <option>Uncovered Lot</option>
                                <option>Covered Lot</option>
                                <option>Backyard</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[14px] font-bold text-gray-900 mb-2">Price hourly (CA$)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-[15px]">$</span>
                                </div>
                                <input type="number" v-model="form.price" step="0.01" min="0"
                                    class="pl-8 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[52px]"
                                    placeholder="4" required />
                            </div>
                        </div>
                        <div>
                            <label class="block text-[14px] font-bold text-gray-900 mb-2">Price monthly (CA$)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-[15px]">$</span>
                                </div>
                                <input type="number" v-model="form.price_monthly" step="0.01" min="0"
                                    class="pl-8 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[52px]"
                                    placeholder="90" />
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200 my-10">

                <!-- Time Availability Section -->
                <div class="mb-4">
                    <h2 class="text-[19px] font-bold text-gray-900 mb-6">Time Availability</h2>

                    <div class="mb-6">
                        <label class="block text-[14px] font-bold text-gray-900 mb-3">Available Days</label>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="day in days" :key="day" type="button" @click="toggleDay(day)" :class="[
                                form.selectedDays.includes(day)
                                    ? 'bg-[#1866ed] border-[#1866ed] text-white'
                                    : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50',
                                'border rounded-full px-5 py-2 text-[14px] font-bold transition-colors'
                            ]">
                                <span class="flex items-center">
                                    <svg v-if="form.selectedDays.includes(day)" class="w-4 h-4 mr-1.5 -ml-1 text-white"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ day }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-[14px] font-bold text-gray-900 mb-3">Standard Hours</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Available From</label>
                                <input type="time" v-model="form.availFrom" :disabled="form.is24_7"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[52px] disabled:bg-gray-100 disabled:text-gray-400" />
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Available To</label>
                                <input type="time" v-model="form.availTo" :disabled="form.is24_7"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[52px] disabled:bg-gray-100 disabled:text-gray-400" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="24-7" type="checkbox" v-model="form.is24_7"
                            class="h-5 w-5 text-[#1866ed] focus:ring-[#1866ed] border-gray-300 rounded">
                        <label for="24-7" class="ml-2.5 block text-[15px] font-medium text-gray-900">
                            Available 24/7
                        </label>
                    </div>
                </div>

                <hr class="border-gray-200 my-10">

                <!-- Additional Points Section -->
                <div>
                    <h2 class="text-[19px] font-bold text-gray-900 mb-6">Features & Rules</h2>

                    <div class="mb-6">
                        <label class="block text-[14px] font-bold text-gray-900 mb-2">Add Additional Details (Bullet
                            Points)</label>
                        <div class="flex">
                            <input type="text" v-model="newPoint" @keydown.enter.prevent="addPoint"
                                class="block w-full rounded-l-lg border-gray-300 shadow-sm border-r-0 focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[52px]"
                                placeholder="e.g. Near the main elevator, Gate code is 1234" />
                            <button type="button" @click="addPoint"
                                class="bg-[#1866ed] text-white px-6 font-bold text-[15px] rounded-r-lg hover:bg-blue-700 transition">
                                Add
                            </button>
                        </div>
                    </div>

                    <div v-if="form.additionalPoints.length > 0"
                        class="mb-8 bg-gray-50 rounded-lg p-5 border border-gray-200">
                        <ul class="space-y-3">
                            <li v-for="(point, index) in form.additionalPoints" :key="index"
                                class="flex items-start justify-between group">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-gray-400 mr-2 shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <circle cx="10" cy="10" r="3"></circle>
                                    </svg>
                                    <span class="text-[15px] text-gray-700 leading-normal">{{ point }}</span>
                                </div>
                                <button type="button" @click="removePoint(index)"
                                    class="text-gray-400 hover:text-red-500 p-1 opacity-0 group-hover:opacity-100 transition focus:opacity-100">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="mb-6">
                        <label class="block text-[14px] font-bold text-gray-900 mb-4">Basic Features</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-4 gap-x-6">
                            <div class="flex items-center">
                                <input id="feat-cctv" type="checkbox" v-model="form.features.cctv"
                                    class="h-5 w-5 text-[#1866ed] focus:ring-[#1866ed] border-gray-300 rounded">
                                <label for="feat-cctv"
                                    class="ml-2.5 block text-[15px] font-medium text-gray-700">CCTV</label>
                            </div>
                            <div class="flex items-center">
                                <input id="feat-ev" type="checkbox" v-model="form.features.evCharging"
                                    class="h-5 w-5 text-[#1866ed] focus:ring-[#1866ed] border-gray-300 rounded">
                                <label for="feat-ev" class="ml-2.5 block text-[15px] font-medium text-gray-700">EV
                                    Charging</label>
                            </div>
                            <div class="flex items-center">
                                <input id="feat-disabled" type="checkbox" v-model="form.features.disabledAccess"
                                    class="h-5 w-5 text-[#1866ed] focus:ring-[#1866ed] border-gray-300 rounded">
                                <label for="feat-disabled"
                                    class="ml-2.5 block text-[15px] font-medium text-gray-700">Disabled Access</label>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200 my-10">

                <!-- Photos Section -->
                <div class="mb-10">
                    <label class="block text-[14px] font-bold text-gray-900 mb-2">Photos</label>
                    <div
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:bg-gray-50 transition cursor-pointer relative">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                viewBox="0 0 48 48" aria-hidden="true">
                                <path
                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="file-upload"
                                    class="relative cursor-pointer bg-transparent rounded-md font-medium text-[#1866ed] hover:text-blue-800 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-[#1866ed]">
                                    <span>Upload photos of your spot</span>
                                    <input id="file-upload" name="file-upload" type="file" class="sr-only" multiple
                                        @change="handleFileUpload">
                                </label>
                            </div>
                            <p class="text-[13px] text-gray-500">PNG, JPG, GIF up to 5MB</p>
                        </div>
                        <div v-if="form?.photos?.length > 0"
                            class="absolute inset-0 bg-white/95 rounded-xl flex items-center justify-center border-2 border-[#1866ed] border-solid">
                            <div class="text-center">
                                <svg class="w-8 h-8 text-[#1866ed] mx-auto mb-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                <p class="text-[15px] font-bold text-[#1866ed]">{{ form.photos.length }} file(s)
                                    selected</p>
                            </div>
                            <input id="file-upload-overlay" name="file-upload-overlay" type="file"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" multiple
                                @change="handleFileUpload">
                        </div>
                    </div>
                    <!-- Display overall photos error -->
                    <div v-if="form.errors.photos" class="mt-2 text-sm text-red-600 font-medium">{{ form.errors.photos
                    }}</div>
                    <!-- Display specific photo errors -->
                    <template v-for="(error, key) in form.errors" :key="key">
                        <div v-if="key.startsWith('photos.')" class="mt-1 text-sm text-red-600 font-medium">{{ error }}
                        </div>
                    </template>
                </div>

                <div class="pt-2">
                    <button type="submit" :disabled="form.processing"
                        class="w-full flex justify-center py-4 px-4 border border-transparent rounded-[10px] shadow-sm text-[16px] font-extrabold text-white bg-[#1866ed] hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1866ed] transition-colors disabled:opacity-50">
                        <span v-if="form.processing">Submitting...</span>
                        <span v-else>Submit Listing</span>
                    </button>
                    <Link href="/"
                        class="mt-4 w-full flex justify-center py-3 px-4 border border-transparent rounded-[10px] text-[15px] font-bold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors">
                        Cancel
                    </Link>
                </div>
            </form>
        </main>

        <!-- Success Confirmation Modal -->
        <transition
            enter-active-class="ease-out duration-300 transition"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200 transition"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showSuccessModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
                <transition
                    enter-active-class="ease-out duration-300 transition transform"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200 transition transform"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div class="bg-white rounded-[24px] max-w-md w-full p-8 shadow-2xl border border-gray-100 text-center relative">
                        <!-- Success Check Icon -->
                        <div class="bg-blue-50 text-[#1866ed] w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        
                        <!-- Header / Notification -->
                        <h3 class="text-[22px] font-extrabold text-gray-900 mb-3 tracking-tight">
                            Host lists their SPOT
                        </h3>
                        
                        <!-- Details Body -->
                        <p class="text-[15px] text-gray-600 leading-relaxed mb-8 px-2 font-medium">
                            Solo Park deducts 15% from your listed rate. Payouts are released after 10 days and processed the first week of each month.
                        </p>
                        
                        <!-- CTA button -->
                        <button 
                            @click="goToMyListings"
                            class="w-full bg-[#1866ed] hover:bg-blue-700 text-white font-extrabold py-4 px-6 rounded-[12px] shadow-lg shadow-blue-100 hover:shadow-blue-200 transition-all duration-150 text-[16px]"
                        >
                            Got It
                        </button>
                    </div>
                </transition>
            </div>
        </transition>
    </div>
</template>
