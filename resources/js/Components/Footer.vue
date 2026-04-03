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
                    Proudly Canadian
                    <!-- <span class="text-base select-none">🇨🇦</span> -->
                </p>
            </div>
        </div>
    </footer>
</template>
