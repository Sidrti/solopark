<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Register" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus
                    autocomplete="name" />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                    autocomplete="username" />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required
                    autocomplete="new-password" />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />

                <TextInput id="password_confirmation" type="password" class="mt-1 block w-full"
                    v-model="form.password_confirmation" required autocomplete="new-password" />

                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-[#1866ed] focus:outline-none focus:ring-2 focus:ring-[#1866ed] focus:ring-offset-2">
                    Already registered?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>

            <div class="mt-4 relative py-2">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-[10px] font-bold uppercase tracking-widest text-gray-400">
                    <span class="bg-white px-2">Or sign up with</span>
                </div>
            </div>

            <div class="mt-4">
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
            </div>
        </form>
    </GuestLayout>
</template>
