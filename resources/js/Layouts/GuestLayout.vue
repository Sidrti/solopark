<script setup>
import Navbar from '@/Components/Navbar.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gray-50 font-sans text-gray-900 selection:bg-[#1866ed] selection:text-white flex flex-col">
        <Navbar :can-login="true" :can-register="true" :is-sticky="false" />

        <div class="flex-1 flex flex-col items-center justify-center pt-10 sm:pt-0 pb-12 px-4 sm:px-0">
            <div class="mb-6 sm:mb-8 text-center mt-8 sm:mt-0">
                <Link href="/" class="text-4xl sm:text-5xl font-extrabold tracking-tighter uppercase text-[#1866ed]">
                    SOLO<span class="text-gray-900 ml-0.5">PARK</span>
                </Link>
                <p class="mt-3 text-[15px] font-medium text-gray-500">Welcome to community parking</p>
            </div>

            <div class="w-full sm:max-w-md px-6 py-8 sm:px-10 sm:py-10 bg-white shadow-xl overflow-hidden sm:rounded-[24px] border border-gray-100">
                <slot />
            </div>
        </div>
    </div>
</template>
