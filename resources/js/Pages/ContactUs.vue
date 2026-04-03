<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import { ref } from 'vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const submitted = ref(false);

const submit = () => {
    // In a real app, this would send an email or store in DB
    // For now, we'll just show a success message
    submitted.value = true;
    form.reset();
};
</script>

<template>

    <Head title="Contact Us - Solopark" />
    <Navbar :can-login="canLogin" :can-register="canRegister" :is-sticky="true" />

    <div class="min-h-screen bg-gray-50 pb-20 pt-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[20px] shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-[#1866ed] px-8 py-10 text-white text-center">
                    <h1 class="text-3xl font-extrabold mb-2">Get in Touch</h1>
                    <p class="text-blue-100 font-medium">Have questions? We're here to help.</p>
                </div>

                <div class="p-8 sm:p-10">
                    <div v-if="submitted" class="bg-green-50 border border-green-200 rounded-[12px] p-6 text-center">
                        <div
                            class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Message Sent!</h2>
                        <p class="text-gray-600">Thank you for reaching out. Our team will get back to you shortly.</p>
                        <button @click="submitted = false" class="mt-6 text-[#1866ed] font-bold hover:underline">Send
                            another message</button>
                    </div>

                    <form v-else @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Your Name</label>
                                <input v-model="form.name" type="text" required
                                    class="w-full rounded-lg border-gray-300 focus:border-[#1866ed] focus:ring-[#1866ed] h-[50px]"
                                    placeholder="Alex Smith" />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                                <input v-model="form.email" type="email" required
                                    class="w-full rounded-lg border-gray-300 focus:border-[#1866ed] focus:ring-[#1866ed] h-[50px]"
                                    placeholder="alex@example.com" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Subject</label>
                            <input v-model="form.subject" type="text" required
                                class="w-full rounded-lg border-gray-300 focus:border-[#1866ed] focus:ring-[#1866ed] h-[50px]"
                                placeholder="How can we help?" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Message</label>
                            <textarea v-model="form.message" rows="5" required
                                class="w-full rounded-lg border-gray-300 focus:border-[#1866ed] focus:ring-[#1866ed] p-4"
                                placeholder="Tell us more about your inquiry..."></textarea>
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-[#1866ed] text-white py-4 px-6 rounded-[12px] font-extrabold text-[16px] hover:bg-blue-700 transition shadow-lg shadow-blue-100">
                            {{ form.processing ? 'Sending...' : 'Send Message' }}
                        </button>
                    </form>

                    <div class="mt-12 pt-10 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-3 gap-8">
                        <div class="text-center">
                            <div
                                class="bg-gray-50 w-10 h-10 rounded-full flex items-center justify-center mx-auto mb-4 text-[#1866ed]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="font-bold text-gray-900 text-sm mb-1">Email Us</h3>
                            <p class="text-gray-500 text-xs">contact@solopark.ca</p>
                        </div>
                        <div class="text-center">
                            <div
                                class="bg-gray-50 w-10 h-10 rounded-full flex items-center justify-center mx-auto mb-4 text-[#1866ed]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <h3 class="font-bold text-gray-900 text-sm mb-1">Office</h3>
                            <p class="text-gray-500 text-xs"> Mississauga, Ontario</p>
                        </div>
                        <div class="text-center">
                            <div
                                class="bg-gray-50 w-10 h-10 rounded-full flex items-center justify-center mx-auto mb-4 text-[#1866ed]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="font-bold text-gray-900 text-sm mb-1">Hours</h3>
                            <p class="text-gray-500 text-xs">Mon-Fri: 9am - 5pm EST</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <Footer />
</template>
