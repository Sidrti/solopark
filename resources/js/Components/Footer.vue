<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="bg-gray-900 text-gray-400 py-14 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class=" border-white/10 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-6">
                    <p>© 2025 SoloPark. All rights reserved.</p>
                    <Link :href="route('contact-us')" class="hover:text-white transition-colors">Contact Us</Link>
                </div>
                <p class="flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 256" class="w-4 h-4">
                        <rect width="128" height="256" fill="#D80621" />
                        <rect x="384" width="128" height="256" fill="#D80621" />
                        <rect x="128" width="256" height="256" fill="#ffffff" />
                        <path fill="#D80621"
                            d="M256 60l18 40 44-10-26 36 36 14-44 10 8 44-36-26-36 26 8-44-44-10 36-14-26-36 44 10z" />
                    </svg>
                    <span>Proudly Canadian</span>
                </p>
            </div>
        </div>
    </footer>
</template>
