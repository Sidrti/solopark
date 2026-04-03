<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
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
    startTime: {
        type: String,
    },
    endTime: {
        type: String,
    },
    days: {
        type: String,
    },
    serviceFee: {
        type: Number,
        default: 5.00
    }
});

const oneTimeStartTime = ref(props.start ? new Date(props.start) : new Date());
const oneTimeEndTime = ref(props.end ? new Date(props.end) : new Date(oneTimeStartTime.value.getTime() + 3 * 60 * 60 * 1000));

const durationMinutes = computed(() => {
    if (props.type === 'one-time') {
        const diffMs = oneTimeEndTime.value.getTime() - oneTimeStartTime.value.getTime();
        const diffMins = Math.ceil(diffMs / (1000 * 60));
        return diffMins > 0 ? diffMins : 1;
    } else {
        // Recurring
        if (!props.startDate || !props.endDate || !props.startTime || !props.endTime) return 0;

        const startDay = new Date(props.startDate + 'T00:00:00');
        const endDay = new Date(props.endDate + 'T00:00:00');
        const selectedDays = props.days ? props.days.split(',') : [];
        if (selectedDays.length === 0) return 0;

        let dayCount = 0;
        let current = new Date(startDay);
        let safety = 0;
        while (current <= endDay && safety < 1000) {
            const dStr = current.toLocaleDateString('en-US', { weekday: 'short' });
            if (selectedDays.includes(dStr)) {
                dayCount++;
            }
            current.setDate(current.getDate() + 1);
            safety++;
        }

        const sParts = props.startTime.split(':');
        const eParts = props.endTime.split(':');
        if (sParts.length < 2 || eParts.length < 2) return 0;

        const [sh, sm] = sParts.map(Number);
        const [eh, em] = eParts.map(Number);
        const dailyDurationMins = (eh * 60 + em) - (sh * 60 + sm);

        return Math.max(dailyDurationMins, 0) * dayCount;
    }
});

const durationUnits = computed(() => Math.ceil(durationMinutes.value / 30));

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

const serviceFeeAmount = computed(() => {
    const rate = props.type === 'monthly' ? 0.30 : 0.10;
    return baseCost.value * rate;
});
const taxAmount = computed(() => (baseCost.value + serviceFeeAmount.value) * 0.13);
const gatewayFeeAmount = computed(() => (baseCost.value + serviceFeeAmount.value + taxAmount.value) * 0.03);
const total = computed(() => baseCost.value + serviceFeeAmount.value + taxAmount.value + gatewayFeeAmount.value);

const formatDateTimeShort = (date) => {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ', ' +
        date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

</script>
<template>

    <Head :title="`${spot.address} - Solopark`" />

    <div class="min-h-screen bg-white font-sans text-gray-900 selection:bg-[#1866ed] selection:text-white pb-20">
        <Navbar :can-login="canLogin" :can-register="canRegister" :is-sticky="true" />

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <!-- Left Details Content -->
                <div class="lg:col-span-2 space-y-8">

                    <div class="rounded-2xl overflow-hidden h-[300px] sm:h-[400px]">
                        <img :src="spot.image" class="w-full h-full object-cover" :alt="spot.address">
                    </div>

                    <div>
                        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900 mb-3">{{
                            spot.address }}</h1>
                        <div class="flex items-center flex-wrap">
                            <div class="flex items-center text-[#1866ed]">
                                <svg class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                    </path>
                                </svg>
                                <span class="font-bold text-[15px]">Verified Spot</span>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-200">

                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-5">Time Availability</h2>
                        <div class="border border-dashed border-gray-300 rounded-xl p-5 sm:p-6 bg-gray-50">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <div class="text-[14px] font-medium text-gray-600 mb-2">Available Days</div>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="day in spot.availDays" :key="day"
                                            class="inline-flex items-center px-3 py-1 bg-[#1866ed] bg-opacity-10 text-[#1866ed] text-[13px] font-bold rounded-full">
                                            {{ day }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-[14px] font-medium text-gray-600 mb-2">Standard Hours</div>
                                    <div class="text-[15px] font-bold text-gray-900">{{ spot.availHours }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-200">

                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-5">Parking Features</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-for="(feature, fidx) in spot.features" :key="f">
                                <div v-if="feature == '1'" class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 mr-3 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-[15px]">{{ fidx }}</span>
                                </div>

                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-200">

                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-5">Good to know</h2>
                        <div class="space-y-4">
                            <div v-for="(point, pidx) in spot.additionalPoints" :key="pidx" class="flex items-start">
                                <svg class="w-[20px] h-[20px] mr-3 text-[#1866ed] shrink-0 mt-0.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-[15px] text-gray-700 leading-relaxed">{{ point }}</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Sticky Sidebar -->
                <div class="relative">
                    <div class="sticky top-[100px] border border-gray-200 rounded-[20px] shadow-lg p-6 sm:p-8 bg-white">
                        <div class="mb-4">
                            <span class="text-3xl font-extrabold text-gray-900">CA${{ spot.price }}</span>
                            <span class="text-gray-500 text-[15px] font-medium ml-1">/ hour</span>
                        </div>
                        <div class="text-[14px] text-gray-500 mb-6">Includes all service fees and taxes</div>

                        <div class="border border-gray-300 rounded-xl overflow-hidden mb-6">
                            <div v-if="type === 'one-time'" class="grid grid-cols-1 sm:grid-cols-2">
                                <div class="p-3 border-b sm:border-b-0 sm:border-r border-gray-300">
                                    <div class="text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1">Start
                                        time</div>
                                    <div class="text-[14px] font-medium text-gray-900">{{
                                        formatDateTimeShort(oneTimeStartTime) }}</div>
                                </div>
                                <div class="p-3">
                                    <div class="text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1">End
                                        time</div>
                                    <div class="text-[14px] font-medium text-gray-900">{{
                                        formatDateTimeShort(oneTimeEndTime) }}</div>
                                </div>
                            </div>
                            <div v-else class="flex flex-col">
                                <div class="p-3 border-b border-gray-300">
                                    <div class="text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1">
                                        Recurring Dates</div>
                                    <div class="text-[14px] font-medium text-gray-900">{{ startDate }} to {{ endDate }}
                                    </div>
                                </div>
                                <div class="p-3 flex justify-between">
                                    <div>
                                        <div class="text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1">
                                            Time</div>
                                        <div class="text-[14px] font-medium text-gray-900">{{ startTime }} - {{ endTime
                                            }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1">
                                            Days</div>
                                        <div class="text-[12px] font-bold text-[#1866ed] uppercase">{{ days }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Breakdown -->
                        <div class="mb-6 space-y-4">
                            <div class="flex justify-between items-center text-[15px]">
                                <span v-if="type === 'monthly'" class="text-gray-600 underline decoration-gray-400 cursor-pointer">CA${{ Number(spot.price_monthly || spot.price).toFixed(0) }} x {{ Math.ceil(Math.round((new Date(endDate) - new Date(startDate)) / (24 * 60 * 60 * 1000)) / 30) }} month(s)</span>
                                <span v-else class="text-gray-600 underline decoration-gray-400 cursor-pointer">CA${{ (spot.price_hourly / 2).toFixed(0) }} x {{ durationUnits }} half-hours</span>
                                <span class="text-gray-900 font-bold">CA${{ baseCost.toFixed(0) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-[15px]">
                                <span class="text-gray-600 underline decoration-gray-400 cursor-pointer">Service fee ({{ type === 'monthly' ? '30%' : '10%' }})</span>
                                <span class="text-gray-900 font-bold">CA${{ serviceFeeAmount.toFixed(0) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-[15px]">
                                <span class="text-gray-600 underline decoration-gray-400 cursor-pointer">Tax (13%)</span>
                                <span class="text-gray-900 font-bold">CA${{ taxAmount.toFixed(0) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-[15px]">
                                <span class="text-gray-600 underline decoration-gray-400 cursor-pointer">Gateway charges (3%)</span>
                                <span class="text-gray-900 font-bold">CA${{ gatewayFeeAmount.toFixed(0) }}</span>
                            </div>
                            <div
                                class="flex justify-between items-center text-[18px] font-extrabold border-t border-gray-200 pt-4 mt-2">
                                <span class="text-gray-900">Total (CAD)</span>
                                <span class="text-[#1866ed]">CA${{ total.toFixed(0) }}</span>
                            </div>
                        </div>

                        <button
                            @click="router.visit(route('spot-book', { id: props.spot.id, type: props.type, start: props.start, end: props.end, startDate: props.startDate, endDate: props.endDate, startTime: props.startTime, endTime: props.endTime, days: props.days }))"
                            class="w-full flex justify-center py-4 px-4 border border-transparent rounded-[12px] shadow-sm text-[16px] font-extrabold text-white bg-[#1866ed] hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1866ed] transition-colors mb-4">
                            Book Now
                        </button>

                        <div class="text-center text-[13px] text-gray-500 font-medium">
                            You won't be charged yet
                        </div>

                    </div>
                </div>

            </div>
        </main>
    </div>
</template>
