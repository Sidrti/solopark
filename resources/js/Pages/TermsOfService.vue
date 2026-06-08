<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const sections = [
    { id: 'about', label: '1. About Solo Park' },
    { id: 'eligibility', label: '2. Eligibility' },
    { id: 'role', label: '3. Platform Role' },
    { id: 'vehicles', label: '4. Permitted Vehicle Types' },
    { id: 'host-resp', label: '5. Host Responsibilities' },
    { id: 'driver-resp', label: '6. Driver Responsibilities' },
    { id: 'booking-payment', label: '7. Bookings & Payment' },
    { id: 'cancellation-overstay', label: '8. Cancellations & Overstays' },
    { id: 'damage-theft', label: '9. Damage, Theft & Risk' },
    { id: 'prohibited', label: '10. Prohibited Use' },
    { id: 'suspension', label: '11. Account Suspension' },
    { id: 'privacy', label: '12. Privacy' },
    { id: 'warranty', label: '13. No Warranty' },
    { id: 'liability', label: '14. Limitation of Liability' },
    { id: 'indemnity', label: '15. Indemnity' },
    { id: 'law', label: '16. Governing Law' },
    { id: 'changes', label: '17. Changes to Terms' },
    { id: 'contact', label: '18. Contact Us' }
];

const scrollToSection = (id) => {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

<template>
    <Head title="Terms of Service - SoloPark" />
    <Navbar :can-login="canLogin" :can-register="canRegister" :is-sticky="true" />

    <div class="min-h-screen bg-gray-50 pb-20 pt-10 font-sans selection:bg-[#1866ed]/20 selection:text-[#1866ed]">
        <!-- Page Header -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
            <div class="bg-gradient-to-r from-[#1866ed] to-[#0d4ec4] rounded-[24px] px-8 py-12 md:py-16 text-white shadow-lg shadow-blue-100 relative overflow-hidden">
                <!-- Decorative Circle -->
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                <div class="absolute -left-10 -top-10 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                
                <div class="max-w-3xl relative z-10">
                    <h1 class="text-3xl sm:text-5xl font-extrabold mb-4 tracking-tight leading-tight">SOLO PARK TERMS OF SERVICE</h1>
                    <p class="text-blue-100 font-semibold text-lg flex items-center gap-2">
                        <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Effective Date: June 03, 2026
                    </p>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
                
                <!-- Sticky Navigation Sidebar -->
                <aside class="lg:col-span-4 xl:col-span-3 lg:sticky lg:top-28 bg-white rounded-[20px] p-6 shadow-sm border border-gray-200 hidden lg:block">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4 px-2">Table of Contents</h2>
                    <nav class="space-y-1 max-h-[70vh] overflow-y-auto pr-2 custom-scrollbar">
                        <button 
                            v-for="section in sections" 
                            :key="section.id"
                            @click="scrollToSection(section.id)"
                            class="w-full text-left text-[14px] font-semibold text-gray-600 hover:text-[#1866ed] hover:bg-blue-50/50 px-3 py-2.5 rounded-xl transition duration-150 ease-in-out flex items-center justify-between group"
                        >
                            <span>{{ section.label }}</span>
                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity text-[#1866ed] shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </nav>
                </aside>

                <!-- Document Body -->
                <main class="lg:col-span-8 xl:col-span-9 bg-white rounded-[24px] p-8 sm:p-12 shadow-sm border border-gray-200">
                    
                    <div class="prose prose-blue max-w-none text-gray-600 leading-relaxed space-y-10">
                        
                        <!-- Welcome Note -->
                        <div class="bg-blue-50/50 border-l-4 border-[#1866ed] rounded-r-2xl p-6 mb-8 text-gray-700">
                            <p class="font-medium text-[16px]">
                                Welcome to <a href="https://solopark.ca" class="text-[#1866ed] font-extrabold hover:underline">SoloPark.ca</a>. These Terms of Service govern your access to and use of the Solo Park website, platform, and related services.
                            </p>
                            <p class="font-semibold text-gray-900 mt-3 text-[16px]">
                                By using Solo Park, you agree to these Terms. If you do not agree, do not use the platform.
                            </p>
                        </div>

                        <!-- Section 1 -->
                        <section id="about" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">1</span>
                                About Solo Park
                            </h2>
                            <div class="space-y-4">
                                <p>
                                    Solo Park is an online platform that helps connect people offering parking spaces (“Hosts”) with people looking to reserve parking (“Drivers” or “Users”).
                                </p>
                                <p class="bg-gray-50 border border-gray-100 rounded-xl p-4 font-medium text-gray-700">
                                    Solo Park is not the owner, operator, manager, insurer, or custodian of listed parking spaces or vehicles. Solo Park does not guarantee the condition, legality, safety, suitability, availability, or accessibility of any parking space.
                                </p>
                            </div>
                        </section>

                        <!-- Section 2 -->
                        <section id="eligibility" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">2</span>
                                Eligibility
                            </h2>
                            <p>
                                You must be at least 18 years old and able to enter into a legally binding agreement to use Solo Park. By using the platform, you confirm that the information you provide is accurate and up to date.
                            </p>
                        </section>

                        <!-- Section 3 -->
                        <section id="role" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">3</span>
                                Platform Role
                            </h2>
                            <p>
                                Solo Park provides a platform for parking listings, bookings, and payments. Solo Park is not a party to any parking arrangement except as the provider of the platform and payment flow. Hosts are responsible for the spaces they list, and Drivers are responsible for how they use those spaces.
                            </p>
                        </section>

                        <!-- Section 4 -->
                        <section id="vehicles" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">4</span>
                                Permitted Vehicle Types
                            </h2>
                            <div class="space-y-4">
                                <p>
                                    Unless a listing clearly states otherwise, Solo Park is intended for standard personal-use vehicles only. Unless expressly approved by the Host and clearly described in the listing, the following are not permitted:
                                </p>
                                <div class="bg-amber-50/50 border border-amber-200 rounded-[16px] p-6">
                                    <h3 class="font-bold text-amber-900 mb-3 flex items-center gap-2 text-[15px]">
                                        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        Prohibited Vehicle Types (unless explicitly approved):
                                    </h3>
                                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-gray-700 font-medium">
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                            Commercial vehicles
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                            Dump trucks
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                            Heavy-duty trucks
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                            Trailers
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                            Boats
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                            RVs or campers
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                            Buses
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                            Construction or industrial equipment
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                            Unregistered or inoperable vehicles
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                            Vehicles carrying hazardous or unlawful materials
                                        </li>
                                    </ul>
                                </div>
                                <p class="font-medium text-gray-700">
                                    Drivers are responsible for ensuring that their vehicle fits the space and complies with all listing rules and property instructions.
                                </p>
                            </div>
                        </section>

                        <!-- Section 5 -->
                        <section id="host-resp" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">5</span>
                                Host Responsibilities
                            </h2>
                            <div class="space-y-4">
                                <p>Hosts must:</p>
                                <ul class="space-y-3">
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>have the legal right to offer the parking space</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>provide accurate descriptions, access instructions, restrictions, and availability</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>disclose any relevant limitations, including gate access, size limitations, snow conditions, shared driveways, blocking risks, or other access concerns</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>ensure that the listing does not violate applicable laws, bylaws, lease terms, condo rules, or property restrictions</span>
                                    </li>
                                </ul>
                                <p class="font-semibold text-gray-800">
                                    Hosts are responsible for the accuracy of their listing and for maintaining their property in a reasonably safe condition.
                                </p>
                            </div>
                        </section>

                        <!-- Section 6 -->
                        <section id="driver-resp" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">6</span>
                                Driver Responsibilities
                            </h2>
                            <div class="space-y-4">
                                <p>Drivers must:</p>
                                <ul class="space-y-3">
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-[#1866ed] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span>provide accurate booking and vehicle information</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-[#1866ed] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span>use the space only for the approved vehicle and approved period</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-[#1866ed] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span>follow all host instructions</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-[#1866ed] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span>not block entrances, sidewalks, garages, or other vehicles</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-[#1866ed] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span>not store prohibited, dangerous, or unlawful materials</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-[#1866ed] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span>not use the space for repairs, business operations, overnight occupancy, or unlawful activity unless expressly allowed</span>
                                    </li>
                                </ul>
                                <p class="bg-gray-50 border border-gray-100 rounded-xl p-4 font-semibold text-gray-700">
                                    Drivers park at their own risk.
                                </p>
                            </div>
                        </section>

                        <!-- Section 7 -->
                        <section id="booking-payment" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">7</span>
                                Bookings and Payment
                            </h2>
                            <div class="space-y-4">
                                <p>
                                    All bookings made through Solo Park must be paid through the platform unless Solo Park expressly approves another method.
                                </p>
                                <p>
                                    By making a booking, the Driver authorizes Solo Park and its payment processor to charge the applicable amount, including fees and taxes where applicable.
                                </p>
                                <p>
                                    Pricing, billing periods, and renewal terms will be shown at checkout or within the booking flow.
                                </p>
                                <p>
                                    Solo Park may cancel, suspend, refuse, or limit bookings where fraud, payment issues, misuse, risk, or policy violations are suspected.
                                </p>

                                <div class="mt-6 bg-blue-50/30 rounded-[16px] p-6 border border-blue-100/50">
                                    <h3 class="text-[17px] font-extrabold text-gray-900 mb-4">Platform Fees and Payouts</h3>
                                    <ul class="space-y-3 text-[14px] text-gray-700">
                                        <li class="flex items-start gap-2.5">
                                            <span class="text-[#1866ed] font-bold mt-0.5">•</span>
                                            <span>Solo Park applies service fees to bookings made through the platform. These fees may vary depending on the type and duration of the booking.</span>
                                        </li>
                                        <li class="flex items-start gap-2.5">
                                            <span class="text-[#1866ed] font-bold mt-0.5">•</span>
                                            <span>Hosts set their own base price for parking.</span>
                                        </li>
                                        <li class="flex items-start gap-2.5">
                                            <span class="text-[#1866ed] font-bold mt-0.5">•</span>
                                            <span>The total amount charged to Drivers may include applicable service fees, taxes, and payment processing costs.</span>
                                        </li>
                                        <li class="flex items-start gap-2.5">
                                            <span class="text-[#1866ed] font-bold mt-0.5">•</span>
                                            <span>Solo Park collects payments from Drivers and facilitates payouts to Hosts.</span>
                                        </li>
                                        <li class="flex items-start gap-2.5">
                                            <span class="text-[#1866ed] font-bold mt-0.5">•</span>
                                            <span>Payouts to Hosts may be adjusted to account for applicable service fees, payment processing costs, monthly payout schedule, refunds, payment processing delays, and platform operations.</span>
                                        </li>
                                        <li class="flex items-start gap-2.5">
                                            <span class="text-[#1866ed] font-bold mt-0.5">•</span>
                                            <span>By using the platform, Hosts agree that the payout amount may differ from the Driver’s total payment.</span>
                                        </li>
                                        <li class="flex items-start gap-2.5">
                                            <span class="text-[#1866ed] font-bold mt-0.5">•</span>
                                            <span>Applicable fees may change from time to time at Solo Park’s discretion.</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                        <!-- Section 8 -->
                        <section id="cancellation-overstay" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">8</span>
                                Cancellations, Refunds, and Overstays
                            </h2>
                            <div class="space-y-4">
                                <p>
                                    Cancellations, refunds, extensions, and overstays are subject to Solo Park’s Refund and Cancellation Policy, which forms part of these Terms.
                                </p>
                                <p class="bg-red-50/50 border border-red-100 rounded-xl p-4 text-red-950 font-medium">
                                    If a Driver remains in a space beyond the booked period, additional fees, penalties, towing, or other enforcement steps may apply where permitted.
                                </p>
                            </div>
                        </section>

                        <!-- Section 9 -->
                        <section id="damage-theft" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">9</span>
                                Damage, Theft, and Risk
                            </h2>
                            <div class="space-y-4">
                                <p>Solo Park is not responsible for:</p>
                                <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-gray-50 border border-gray-100 rounded-[16px] p-6 text-gray-700 font-medium">
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        theft
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        vandalism
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        weather damage
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        property damage
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        towing by third parties
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        bylaw tickets
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        disputes between Hosts and Drivers
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        inaccurate listings/access issues
                                    </li>
                                    <li class="flex items-center gap-2 md:col-span-2">
                                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        damage caused by other users or third parties
                                    </li>
                                </ul>
                                <p>
                                    Drivers are responsible for damage they cause to a Host’s property or surrounding area.
                                </p>
                                <p>
                                    Hosts are responsible for reporting any damage or incident promptly, with supporting photos and details where possible.
                                </p>
                                <p class="font-semibold text-gray-800">
                                    Solo Park is not an insurer and does not provide vehicle, property, or liability insurance coverage unless expressly stated.
                                </p>
                            </div>
                        </section>

                        <!-- Section 10 -->
                        <section id="prohibited" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">10</span>
                                Prohibited Use
                            </h2>
                            <div class="space-y-4">
                                <p>You may not:</p>
                                <ul class="space-y-3">
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        <span>use Solo Park for unlawful purposes</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        <span>provide false information</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        <span>interfere with the platform or payment systems</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        <span>impersonate another person</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        <span>post misleading listings</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        <span>use the platform in a way that creates safety, legal, or reputational risk for Solo Park or others</span>
                                    </li>
                                </ul>
                                <p class="font-semibold text-gray-800">
                                    Solo Park may suspend or terminate access for any violation of these Terms.
                                </p>
                            </div>
                        </section>

                        <!-- Section 11 -->
                        <section id="suspension" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">11</span>
                                Account Suspension or Removal
                            </h2>
                            <p>
                                Solo Park may suspend, restrict, or remove any account, listing, booking, or user at its discretion where necessary to address payment issues, complaints, suspected fraud, safety concerns, repeated late payment, misuse, or legal risk.
                            </p>
                        </section>

                        <!-- Section 12 -->
                        <section id="privacy" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">12</span>
                                Privacy
                            </h2>
                            <p>
                                Your use of Solo Park is also subject to our Privacy Policy, which explains how personal information is collected, used, stored, and disclosed.
                            </p>
                        </section>

                        <!-- Section 13 -->
                        <section id="warranty" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">13</span>
                                No Warranty
                            </h2>
                            <p>
                                Solo Park provides the platform on an “as is” and “as available” basis.
                            </p>
                            <p class="font-semibold text-gray-800">
                                To the fullest extent permitted by law, Solo Park makes no guarantees or warranties regarding uninterrupted service, listing accuracy, booking success, availability, property condition, or user conduct.
                            </p>
                        </section>

                        <!-- Section 14 -->
                        <section id="liability" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">14</span>
                                Limitation of Liability
                            </h2>
                            <div class="space-y-4">
                                <p>
                                    To the fullest extent permitted by law, Solo Park and its owners, officers, affiliates, contractors, and service providers are not liable for any indirect, incidental, special, consequential, or punitive damages, or for any loss arising out of:
                                </p>
                                <ul class="space-y-2 text-[15px] font-medium text-gray-700">
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                        use of the platform
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                        inability to use the platform
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                        disputes between users
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                        damage to vehicles or property
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                        missed bookings
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                        payment disputes
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                        unauthorized access or misuse
                                    </li>
                                </ul>
                                <p class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 font-semibold text-gray-800">
                                    If Solo Park is found liable for any claim, its total liability will not exceed the amount paid to Solo Park in connection with the booking giving rise to the claim, to the extent permitted by law.
                                </p>
                            </div>
                        </section>

                        <!-- Section 15 -->
                        <section id="indemnity" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">15</span>
                                Indemnity
                            </h2>
                            <div class="space-y-4">
                                <p>
                                    You agree to indemnify and hold harmless Solo Park and its affiliates, officers, contractors, and representatives from any claims, losses, liabilities, damages, or expenses arising from:
                                </p>
                                <ul class="space-y-2 text-[15px] font-medium text-gray-700">
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                        your use of the platform
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                        your violation of these Terms
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                        your listing, booking, vehicle, property, or conduct
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                        your violation of any law or third-party rights
                                    </li>
                                </ul>
                            </div>
                        </section>

                        <!-- Section 16 -->
                        <section id="law" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">16</span>
                                Governing Law
                            </h2>
                            <p>
                                These Terms are governed by the laws of the Province of Ontario and the laws of Canada applicable in Ontario.
                            </p>
                            <p class="font-semibold text-gray-800">
                                Any dispute arising from these Terms or the use of Solo Park will be subject to the courts of Ontario, unless applicable law requires otherwise.
                            </p>
                        </section>

                        <!-- Section 17 -->
                        <section id="changes" class="scroll-mt-28 border-b border-gray-100 pb-8">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">17</span>
                                Changes to These Terms
                            </h2>
                            <p>
                                Solo Park may update these Terms from time to time. Updated Terms will be posted on the website with a revised effective date. Continued use of the platform after changes are posted means you accept the updated Terms.
                            </p>
                        </section>

                        <!-- Section 18 -->
                        <section id="contact" class="scroll-mt-28">
                            <h2 class="text-2xl font-extrabold text-gray-900 mb-4 flex items-center gap-3">
                                <span class="bg-[#1866ed]/10 text-[#1866ed] w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold">18</span>
                                Contact
                            </h2>
                            <div class="bg-blue-50/50 border border-blue-100 rounded-[16px] p-6 space-y-4">
                                <p class="text-gray-700 font-medium">
                                    Questions about these Terms can be sent through <a href="https://solopark.ca" class="text-[#1866ed] font-extrabold hover:underline">SoloPark.ca</a> or by contacting Solo Park at:
                                </p>
                                <a 
                                    href="mailto:contact@solopark.ca" 
                                    class="inline-flex items-center gap-3 bg-white border border-gray-200 px-5 py-3.5 rounded-xl text-gray-900 font-extrabold hover:border-[#1866ed] hover:text-[#1866ed] transition shadow-sm"
                                >
                                    <svg class="w-5 h-5 text-[#1866ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    contact@solopark.ca
                                </a>
                            </div>
                        </section>

                    </div>
                </main>

            </div>
        </div>
    </div>

    <Footer />
</template>

<style scoped>
/* Smooth scrollbar for Table of Contents */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
