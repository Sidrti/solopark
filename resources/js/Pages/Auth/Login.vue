<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Log in" />

        <!-- Status message -->
        <div v-if="status"
            class="mb-5 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                    clip-rule="evenodd" />
            </svg>
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">

            <!-- Email -->
            <div class="group">
                <InputLabel for="email" value="Email address"
                    class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase" />
                <div class="relative">
                    <span
                        class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400 group-focus-within:text-[#1866ed] transition-colors duration-150">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </span>
                    <TextInput id="email" type="email"
                        class="block w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#1866ed] focus:bg-white focus:ring-2 focus:ring-[#1866ed]/20 focus:outline-none"
                        v-model="form.email" placeholder="you@example.com" required autofocus autocomplete="username" />
                </div>
                <InputError class="mt-1.5 text-xs" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div class="group">
                <div class="mb-1.5 flex items-center justify-between">
                    <InputLabel for="password" value="Password"
                        class="block text-xs font-semibold tracking-wide text-gray-500 uppercase" />
                    <Link v-if="canResetPassword" :href="route('password.request')"
                        class="text-xs font-medium text-[#1866ed] hover:text-blue-700 transition-colors duration-150">
                        Forgot password?
                    </Link>
                </div>
                <div class="relative">
                    <span
                        class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400 group-focus-within:text-[#1866ed] transition-colors duration-150">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </span>
                    <TextInput id="password" type="password"
                        class="block w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#1866ed] focus:bg-white focus:ring-2 focus:ring-[#1866ed]/20 focus:outline-none"
                        v-model="form.password" placeholder="••••••••" required autocomplete="current-password" />
                </div>
                <InputError class="mt-1.5 text-xs" :message="form.errors.password" />
            </div>

            <!-- Remember me -->
            <div class="flex items-center gap-2.5">
                <Checkbox id="remember" name="remember" v-model:checked="form.remember"
                    class="h-4 w-4 rounded border-gray-300 text-[#1866ed] focus:ring-[#1866ed]/30" />
                <label for="remember" class="cursor-pointer select-none text-sm text-gray-600">
                    Keep me signed in
                </label>
            </div>

            <!-- Actions -->
            <div class="pt-1 space-y-3">
                <PrimaryButton
                    class="relative w-full justify-center rounded-xl bg-[#1866ed] py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-200 transition hover:bg-blue-700 hover:shadow-blue-300 active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed"
                    :class="{ 'opacity-40': form.processing }" :disabled="form.processing">
                    <span v-if="form.processing" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        Signing in…
                    </span>
                    <span v-else>Sign in</span>
                </PrimaryButton>

                <div class="relative py-2">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div
                        class="relative flex justify-center text-[10px] font-bold uppercase tracking-widest text-gray-400">
                        <span class="bg-[#fcfdff] px-2">Or continue with</span>
                    </div>
                </div>

                <a :href="route('auth.google')"
                    class="flex w-full items-center justify-center gap-2.5 rounded-xl border border-gray-200 bg-white py-2.5 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50 hover:border-gray-300 active:scale-[0.98]">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20">
                        <path fill="#EA4335"
                            d="M24 9.5c3.54 0 6.73 1.22 9.24 3.61l6.9-6.9C35.64 2.3 30.2 0 24 0 14.62 0 6.36 5.48 2.56 13.44l8.04 6.24C12.52 13.1 17.77 9.5 24 9.5z" />

                        <path fill="#34A853"
                            d="M46.5 24c0-1.64-.15-3.21-.42-4.72H24v9.02h12.7c-.55 2.96-2.2 5.47-4.7 7.16l7.24 5.62C43.84 36.54 46.5 30.83 46.5 24z" />

                        <path fill="#4285F4"
                            d="M10.6 28.32A14.5 14.5 0 0 1 9.5 24c0-1.5.26-2.95.73-4.32l-8.04-6.24A23.88 23.88 0 0 0 0 24c0 3.85.92 7.49 2.56 10.56l8.04-6.24z" />

                        <path fill="#FBBC05"
                            d="M24 48c6.48 0 11.92-2.13 15.89-5.78l-7.24-5.62c-2.02 1.36-4.6 2.15-8.65 2.15-6.23 0-11.48-3.6-13.4-8.82l-8.04 6.24C6.36 42.52 14.62 48 24 48z" />
                    </svg>
                    Google
                </a>

                <p class="text-center text-sm text-gray-500">
                    Don't have an account?
                    <Link :href="route('register')"
                        class="font-semibold text-[#1866ed] hover:text-blue-700 transition-colors duration-150">
                        Create one
                    </Link>
                </p>
            </div>

        </form>
    </GuestLayout>
</template>