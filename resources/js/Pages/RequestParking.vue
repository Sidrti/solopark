<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const submitted = ref(false);
const form = useForm({ location: '', parking_type: 'daily', start_date: '', start_time: '',
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone, budget: '', name: '', email: '', phone: '' });
const submit = () => form.post(route('parking-requests.store'), {
    preserveScroll: true,
    onSuccess: () => { submitted.value = true; form.reset(); },
});
const fields = [
    { key: 'location', label: 'Preferred location or postal code', type: 'text', max: 255 },
    { key: 'start_date', label: 'Required start date', type: 'date' },
    { key: 'start_time', label: 'Required start time (local to you)', type: 'time' },
    { key: 'budget', label: 'Approximate budget (CAD, optional)', type: 'number' },
    { key: 'name', label: 'Name', type: 'text', max: 255 },
    { key: 'email', label: 'Email', type: 'email', max: 255 },
    { key: 'phone', label: 'Phone number', type: 'tel', max: 30 },
];
</script>

<template>
    <Head title="Request Parking" />
    <Navbar :can-login="true" :can-register="true" />
    <main class="bg-gray-50 min-h-screen py-12 px-4">
        <div class="max-w-xl mx-auto bg-white p-8 rounded-2xl border border-gray-200 shadow-sm">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-3">Request Parking</h1>
            <p class="text-gray-600 mb-6">Can't find parking nearby? Tell us what you need and we'll try to find a suitable host. No payment or booking is required.</p>
            <div v-if="submitted" role="status" class="bg-green-50 text-green-800 rounded-xl p-5">
                Your request has been sent. Our team will contact you if we find a suitable host.
                <button class="block mt-4 underline font-semibold" @click="submitted = false">Submit another request</button>
            </div>
            <form v-else @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="parking_type" class="block font-semibold mb-2">Parking type</label>
                    <select id="parking_type" v-model="form.parking_type" class="w-full rounded-lg border-gray-300">
                        <option v-for="type in ['hourly', 'daily', 'overnight', 'monthly']" :key="type" :value="type">{{ type[0].toUpperCase() + type.slice(1) }}</option>
                    </select>
                    <p v-if="form.errors.parking_type" class="text-red-600 text-sm">{{ form.errors.parking_type }}</p>
                </div>
                <div v-for="field in fields" :key="field.key">
                    <label :for="field.key" class="block font-semibold mb-2">{{ field.label }}</label>
                    <input :id="field.key" v-model="form[field.key]" :type="field.type" :required="field.key !== 'budget'"
                        :maxlength="field.max" :min="field.type === 'number' ? 0 : undefined" :step="field.type === 'number' ? '0.01' : undefined"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600" />
                    <p v-if="form.errors[field.key]" class="text-red-600 text-sm">{{ form.errors[field.key] }}</p>
                </div>
                <p class="text-sm text-gray-500">Start time zone: {{ form.timezone }}. Your contact details will be used to follow up on this request.</p>
                <p v-if="form.errors.timezone || form.errors.submission" role="alert" class="text-red-600">{{ form.errors.submission || form.errors.timezone }}</p>
                <button :disabled="form.processing" class="w-full bg-[#1866ed] text-white py-3 rounded-xl font-bold disabled:opacity-50">{{ form.processing ? 'Sending…' : 'Send parking request' }}</button>
            </form>
        </div>
    </main>
    <Footer />
</template>
