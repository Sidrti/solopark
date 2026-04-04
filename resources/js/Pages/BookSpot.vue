<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Navbar from '@/Components/Navbar.vue';

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    spot: {
        type: Object,
        required: true
    },
    type: {
        type: String,
        default: 'one-time'
    },
    start: {
        type: String,
        default: null
    },
    end: {
        type: String,
        default: null
    },
    startDate: {
        type: String,
    },
    endDate: {
        type: String,
    },
    startTimeForm: {
        type: String,
    },
    endTimeForm: {
        type: String,
    },
    days: {
        type: String,
    },
    serviceFee: {
        type: Number,
        default: 5.00
    },
    vehicles: {
        type: Array,
        default: () => []
    },
    stripeKey: {
        type: String,
        required: true
    }
});

import { loadStripe } from '@stripe/stripe-js';
import { onMounted } from 'vue';

const stripe = ref(null);
const elements = ref(null);
const paymentElement = ref(null);
const stripeError = ref(null);
const isProcessing = ref(false);

onMounted(async () => {
    stripe.value = await loadStripe(props.stripeKey);

    // We'll initialize elements only after we have the amount, 
    // but we can't do it yet because we need a clientSecret.
    // Actually, we can initialize it with the amount if we use server-side creation.
});

// We need to fetch the clientSecret as soon as the page loads or when details are ready
const clientSecret = ref(null);

const initPaymentElement = async () => {
    try {
        const response = await fetch(route('bookings.payment-intent'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                amount: total.value,
                spot_id: props.spot.id
            })
        });
        const data = await response.json();
        clientSecret.value = data.clientSecret;

        elements.value = stripe.value.elements({ clientSecret: clientSecret.value });
        paymentElement.value = elements.value.create('payment');
        paymentElement.value.mount('#payment-element');
    } catch (e) {
        console.error('Error initializing payment element:', e);
        stripeError.value = 'Failed to load payment system. Please refresh.';
    }
};

onMounted(() => {
    // Small delay to ensure the DOM element is ready
    setTimeout(initPaymentElement, 500);
});

// Form states
const selectedVehicleId = ref('new');
const isAddingVehicle = computed(() => selectedVehicleId.value === 'new');

const getSelectedVehicle = () => {
    return props.vehicles.find(v => v.id === selectedVehicleId.value) || {};
};

const vehicleForm = useForm({
    license_plate: '',
    make_model: ''
});

const saveVehicle = () => {
    vehicleForm.post(route('vehicles.store'), {
        preserveScroll: true,
        onSuccess: () => {
            if (props.vehicles && props.vehicles.length > 0) {
                // Auto select the newly added vehicle (the last one usually, but we'll find by plate)
                const newlyAdded = props.vehicles.find(v => v.license_plate === vehicleForm.license_plate.toUpperCase());
                if (newlyAdded) {
                    selectedVehicleId.value = newlyAdded.id;
                }
            }
            vehicleForm.reset();
        }
    });
};
const mobileNumber = ref('');
const mobileError = ref('');

// Validate and Format Canadian Mobile Number
const formatMobile = () => {
    // Strip non-digits and limit to 10 chars
    const cleaned = mobileNumber.value.replace(/\D/g, '').substring(0, 10);

    // Auto-format to (XXX) XXX-XXXX
    const match = cleaned.match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
    if (!match) {
        mobileNumber.value = '';
    } else {
        mobileNumber.value = !match[2] ? match[1] : '(' + match[1] + ') ' + match[2] + (match[3] ? '-' + match[3] : '');
    }

    validateMobile();
};

const validateMobile = () => {
    const cleaned = mobileNumber.value.replace(/\D/g, '');
    const isValid = cleaned.length === 10;

    if (!isValid && mobileNumber.value.length > 0) {
        mobileError.value = 'Please enter a valid 10-digit Canadian mobile number.';
        return false;
    }

    mobileError.value = '';
    return isValid;
};

const confirmBooking = async () => {
    if (isAddingVehicle.value) {
        alert('Please save your vehicle to your profile before completing the reservation.');
        return;
    }

    if (!mobileNumber.value) {
        mobileError.value = 'Mobile number is required.';
        return;
    }

    if (!validateMobile()) return;

    isProcessing.value = true;
    stripeError.value = null;

    try {
        // 1. Confirm the payment with Stripe
        const { error, paymentIntent } = await stripe.value.confirmPayment({
            elements: elements.value,
            redirect: 'if_required',
        });

        if (error) {
            stripeError.value = error.message;
            isProcessing.value = false;
            return;
        }

        if (paymentIntent.status === 'succeeded') {
            // 2. Finalize the booking on our backend
            const formData = {
                spot_id: props.spot.id,
                vehicle_id: selectedVehicleId.value,
                mobile_number: mobileNumber.value,
                subtotal: baseCost.value,
                service_fee: calculatedServiceFee.value,
                tax: tax.value,
                gateway_fee: gatewayFee.value,
                total_price: total.value,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                type: props.type,
                payment_intent_id: paymentIntent.id
            };

            if (props.type === 'one-time') {
                formData.start_time = startTime.value.toISOString();
                formData.end_time = endTime.value.toISOString();
            } else {
                formData.startDate = props.startDate;
                formData.endDate = props.endDate;
                formData.startTime = props.startTimeForm;
                formData.endTime = props.endTimeForm;
                formData.days = props.days;
            }

            router.post(route('bookings.store'), formData, {
                onFinish: () => {
                    isProcessing.value = false;
                }
            });
        }
    } catch (e) {
        console.error('Payment confirmation error:', e);
        stripeError.value = 'An unexpected error occurred. Please try again.';
        isProcessing.value = false;
    }
};

const startTime = ref(props.start ? new Date(props.start) : new Date());
const endTime = ref(props.end ? new Date(props.end) : new Date(startTime.value.getTime() + 3 * 60 * 60 * 1000));

const durationMinutes = computed(() => {
    if (props.type === 'one-time') {
        const diffMs = (endTime.value && startTime.value) ? endTime.value.getTime() - startTime.value.getTime() : 0;
        const diffMins = Math.ceil(diffMs / (1000 * 60));
        return diffMins > 0 ? diffMins : 1;
    } else {
        // Recurring
        if (!props.startDate || !props.endDate || !props.startTimeForm || !props.endTimeForm) return 0;

        const startDay = new Date(props.startDate + 'T00:00:00');
        const endDay = new Date(props.endDate + 'T00:00:00');
        const selectedDays = props.days ? props.days.split(',') : [];
        if (selectedDays.length === 0) return 0;

        let dayCount = 0;
        let current = new Date(startDay);
        // Safety limit to prevent infinite loops if dates are invalid
        let safety = 0;
        while (current <= endDay && safety < 1000) {
            const dStr = current.toLocaleDateString('en-US', { weekday: 'short' }); // "Mon", "Tue"
            if (selectedDays.includes(dStr)) {
                dayCount++;
            }
            current.setDate(current.getDate() + 1);
            safety++;
        }

        const sParts = props.startTimeForm.split(':');
        const eParts = props.endTimeForm.split(':');
        if (sParts.length < 2 || eParts.length < 2) return 0;

        const [sh, sm] = sParts.map(Number);
        const [eh, em] = eParts.map(Number);
        const dailyDurationMins = (eh * 60 + em) - (sh * 60 + sm);

        return Math.max(dailyDurationMins, 0) * dayCount;
    }
});

const durationUnits = computed(() => {
    return Math.ceil(durationMinutes.value / 30);
});

const baseCost = computed(() => {
    if (props.type === 'monthly' && props.startDate && props.endDate) {
        const start = new Date(props.startDate);
        const end = new Date(props.endDate);
        const diffDays = Math.round((end - start) / (24 * 60 * 60 * 1000));
        const months = Math.ceil(diffDays / 30);
        return (props.spot.price_monthly || props.spot.price) * months;
    }
    return (props.spot.price_hourly / 2) * durationUnits.value;
});
const calculatedServiceFee = computed(() => {
    const rate = props.type === 'monthly' ? 0.30 : 0.10;
    return baseCost.value * rate;
});
const tax = computed(() => (baseCost.value + calculatedServiceFee.value) * 0.13);
const gatewayFee = computed(() => (baseCost.value + calculatedServiceFee.value + tax.value) * 0.03);
const total = computed(() => baseCost.value + calculatedServiceFee.value + tax.value + gatewayFee.value);

const formatDateTimeShort = (date) => {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ', ' +
        date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

</script>
<template>

    <Head title="Confirm Reservation - Solopark" />

    <div class="min-h-screen bg-gray-50 font-sans text-gray-900 selection:bg-[#1866ed] selection:text-white pb-20">
        <Navbar :can-login="canLogin" :can-register="canRegister" :is-sticky="true" />

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            <button
                @click="router.visit(route('spot-details', { id: spot.id, type: type, start: start, end: end, startDate: startDate, endDate: endDate, startTime: startTimeForm, endTime: endTimeForm, days: days }))"
                class="flex items-center text-[15px] font-bold text-gray-600 hover:text-gray-900 mb-6 transition">
                <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to details
            </button>

            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900 mb-8 sm:mb-10">Confirm your
                reservation</h1>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
                <!-- Left Details Content -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- Vehicle Info -->
                    <div class="bg-white rounded-[20px] p-6 sm:p-8 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between xl:flex-row flex-col mb-6">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center xl:mb-0 mb-4 xl:w-1/2 w-full">
                                <svg class="w-6 h-6 mr-3 text-[#1866ed]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                Vehicle Information
                            </h2>

                            <div v-if="vehicles.length > 0" class="xl:w-1/2 w-full">
                                <select v-model="selectedVehicleId"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[50px]">
                                    <option value="new">+ Add new vehicle</option>
                                    <option v-for="v in vehicles" :key="v.id" :value="v.id">
                                        {{ v.license_plate }} ({{ v.make_model }})
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div v-if="isAddingVehicle" class="grid grid-cols-1 sm:grid-cols-2 gap-6 relative">
                            <div>
                                <label class="block text-[14px] font-bold text-gray-900 mb-2">License Plate</label>
                                <input type="text" v-model="vehicleForm.license_plate"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[50px] uppercase placeholder-gray-400"
                                    placeholder="ABC-1234" />
                                <p v-if="vehicleForm.errors.license_plate" class="text-red-500 text-xs mt-1">{{
                                    vehicleForm.errors.license_plate }}</p>
                            </div>
                            <div>
                                <label class="block text-[14px] font-bold text-gray-900 mb-2">Vehicle Make/Model</label>
                                <input type="text" v-model="vehicleForm.make_model"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed] sm:text-[15px] h-[50px] placeholder-gray-400"
                                    placeholder="Toyota Camry" />
                                <p v-if="vehicleForm.errors.make_model" class="text-red-500 text-xs mt-1">{{
                                    vehicleForm.errors.make_model }}</p>
                            </div>

                            <div class="sm:col-span-2 flex justify-end">
                                <button type="button" @click="saveVehicle" :disabled="vehicleForm.processing"
                                    class="text-[15px] font-bold text-[#1866ed] hover:text-blue-800 transition-colors flex items-center">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                                        </path>
                                    </svg>
                                    {{ vehicleForm.processing ? 'Saving...' : 'Save Vehicle to Profile' }}
                                </button>
                            </div>
                        </div>

                        <div v-else class="bg-gray-50 rounded-xl p-5 border border-gray-200 flex items-center">
                            <div class="bg-blue-100 p-3 rounded-full mr-4 text-[#1866ed]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <p
                                    class="text-[16px] font-extrabold text-gray-900 border border-gray-300 rounded px-2 py-0.5 inline-block bg-white shadow-sm mb-1 tracking-wider">
                                    {{ getSelectedVehicle().license_plate }}</p>
                                <p class="text-[14px] text-gray-500 font-medium">{{ getSelectedVehicle().make_model }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="bg-white rounded-[20px] p-6 sm:p-8 shadow-sm border border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-6 h-6 mr-3 text-[#1866ed]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                            Contact Information
                        </h2>

                        <div class="mb-2">
                            <label class="block text-[14px] font-bold text-gray-900 mb-2">Mobile Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-[15px]">+1</span>
                                    <div class="h-5 w-px bg-gray-300 mx-2"></div>
                                </div>
                                <input type="tel" v-model="mobileNumber" @input="formatMobile" :class="[
                                    'pl-[52px] block w-full rounded-lg shadow-sm sm:text-[15px] h-[50px] placeholder-gray-400',
                                    mobileError ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#1866ed] focus:ring-[#1866ed]'
                                ]" placeholder="(555) 000-0000" />
                            </div>
                            <p v-if="mobileError" class="mt-2 text-sm text-red-600 font-medium">{{ mobileError }}</p>
                        </div>
                        <p class="text-[13px] text-gray-500 mt-2">We'll send your booking confirmation and any access
                            codes to this number.</p>
                    </div>

                    <!-- Payment Section -->
                    <div class="bg-white rounded-[20px] p-6 sm:p-8 shadow-sm border border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-6 h-6 mr-3 text-[#1866ed]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                </path>
                            </svg>
                            Payment Method
                        </h2>

                        <div v-if="!clientSecret" class="flex flex-col items-center py-8">
                            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#1866ed] mb-4"></div>
                            <p class="text-gray-500 text-sm font-medium">Initializing secure payment...</p>
                        </div>

                        <div id="payment-element" class="mt-4 min-h-[100px]">
                            <!-- Stripe Elements will be injected here -->
                        </div>
                        <div v-if="stripeError"
                            class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center text-red-700 text-sm font-medium">
                            <svg class="w-5 h-5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ stripeError }}
                        </div>
                    </div>

                </div>

                <!-- Right Sticky Sidebar -->
                <div class="lg:col-span-5 relative">
                    <div class="sticky top-24 bg-white border border-gray-200 rounded-[20px] shadow-sm p-6 sm:p-8">

                        <!-- Spot Summary -->
                        <div class="flex items-start mb-6">
                            <img :src="spot.image" class="w-[100px] h-[75px] rounded-lg object-cover mr-4" alt="Spot">
                            <div>
                                <h3 class="text-[16px] font-bold text-gray-900 leading-tight mb-1 line-clamp-2">{{
                                    spot.address }}</h3>
                                <div class="text-[13px] font-medium text-gray-500">{{ spot.city }}</div>
                            </div>
                        </div>

                        <hr class="border-gray-200 mb-6">

                        <h4 class="text-[16px] font-bold text-gray-900 mb-4">Reservation Details</h4>
                        <div class="space-y-3 mb-6">
                            <template v-if="type === 'one-time'">
                                <div class="flex justify-between items-center text-[15px]">
                                    <span class="text-gray-500 font-medium tracking-wide">Starts</span>
                                    <span class="text-gray-900 font-bold">{{ formatDateTimeShort(startTime) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-[15px]">
                                    <span class="text-gray-500 font-medium tracking-wide">Ends</span>
                                    <span class="text-gray-900 font-bold">{{ formatDateTimeShort(endTime) }}</span>
                                </div>
                            </template>
                            <template v-else-if="type === 'recurring'">
                                <div class="flex flex-col space-y-2">
                                    <div class="flex justify-between items-center text-[15px]">
                                        <span class="text-gray-500 font-medium tracking-wide">Recurring Booking</span>
                                        <span class="text-gray-900 font-bold">{{ props.startDate }} to {{ props.endDate
                                            }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-[13px]">
                                        <span class="text-[#1866ed] font-bold">{{ props.startTimeForm }} - {{
                                            props.endTimeForm }}</span>
                                        <span class="text-gray-500 font-medium uppercase truncate ml-2 max-w-[150px]">{{
                                            props.days }}</span>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex justify-between items-center text-[15px]">
                                    <span class="text-gray-500 font-medium tracking-wide">Monthly Booking</span>
                                    <span class="text-gray-900 font-bold">{{ props.startDate }} to {{ props.endDate
                                        }}</span>
                                </div>
                            </template>
                        </div>

                        <hr class="border-gray-200 mb-6">

                        <!-- Price Breakdown removed as requested -->

                        <hr class="border-gray-200 mb-5">

                        <div class="flex justify-between items-center text-[18px] font-extrabold mb-8">
                            <span class="text-gray-900">Total (CAD)</span>
                            <span class="text-[#1866ed]">CA${{ total.toFixed(0) }}</span>
                        </div>

                        <button @click="confirmBooking" :disabled="isProcessing || !clientSecret"
                            class="w-full flex items-center justify-center py-4 px-4 border border-transparent rounded-[12px] shadow-sm text-[16px] font-extrabold text-white bg-[#1866ed] hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1866ed] transition-colors mb-4 disabled:opacity-70 disabled:cursor-not-allowed">
                            <span v-if="isProcessing" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Processing...
                            </span>
                            <span v-else>Complete Reservation</span>
                        </button>

                        <!-- <p class="text-center text-[12px] text-gray-500 leading-relaxed px-2 font-medium mt-4">
                            By clicking "Complete Reservation", you agree to Solopark's <a href="#"
                                class="underline hover:text-gray-800">Terms of Service</a> and <a href="#"
                                class="underline hover:text-gray-800">Privacy Policy</a>.
                        </p> -->
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>
