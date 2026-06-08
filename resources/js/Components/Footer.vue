<script setup>
import { Link } from '@inertiajs/vue3';
import logo1 from '../../images/logo1.jpg';
</script>

<template>
    <footer class="bg-gray-900 text-gray-400 pt-16 pb-12 px-4 sm:px-6 lg:px-8 border-t border-white/5">
        <div class="max-w-7xl mx-auto">
            <!-- Top Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12 text-sm">
                <!-- Branding -->
                <div class="space-y-4">
                    <Link href="/" class="inline-block">
                        <img :src="logo1" alt="Logo" class="w-[130px] ">
                    </Link>
                    <p class="text-xs text-gray-500 max-w-xs leading-relaxed">
                        Solo Park is Canada's premier parking sharing platform, connecting drivers with convenient spaces.
                    </p>
                </div>

                <!-- Navigation -->
                <div class="space-y-4">
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider">Quick Links</h4>
                    <ul class="space-y-2.5 text-xs font-semibold">
                        <li>
                            <Link :href="route('home')" class="hover:text-white transition-colors">Home</Link>
                        </li>
                        <li>
                            <Link :href="route('contact-us')" class="hover:text-white transition-colors">Contact Us</Link>
                        </li>
                        <li>
                            <Link :href="route('terms')" class="hover:text-white transition-colors">Terms of Service</Link>
                        </li>
                    </ul>
                </div>

                <!-- Contact Details -->
                <div class="space-y-4">
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider">Contact Info</h4>
                    <ul class="space-y-3 text-xs leading-relaxed font-semibold">
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-[#1866ed] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>200 Matheson Blvd W, Mississauga ON L5R 3L7</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[#1866ed] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <a href="tel:6474950720" class="hover:text-white transition-colors">(647) 495-0720</a>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[#1866ed] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <a href="mailto:contact@solopark.ca" class="hover:text-white transition-colors">contact@solopark.ca</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Border & Copyright Info -->
            <div class="border-t border-white/5 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold">
                <p>© 2025 SoloPark. All rights reserved.</p>
                <p class="flex items-center gap-2 text-gray-500">
                    <img src="https://flagcdn.com/w40/ca.png" srcset="https://flagcdn.com/w80/ca.png 2x" width="28" height="14" alt="Canada" class="rounded-sm opacity-90">
                    <span>Proudly Canadian</span>
                </p>
            </div>
        </div>
    </footer>
</template>
