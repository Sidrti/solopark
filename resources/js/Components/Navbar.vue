<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import logo1 from '../../images/logo1.jpg';

const showingMobileMenu = ref(false);

defineProps({
    canLogin: {
        type: Boolean,
        default: true
    },
    canRegister: {
        type: Boolean,
        default: true
    },
    isSticky: {
        type: Boolean,
        default: true
    },
    containerClass: {
        type: String,
        default: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'
    }
});
</script>

<template>
    <nav :class="[isSticky ? 'sticky top-0' : 'flex-shrink-0', 'border-b border-gray-200 bg-white z-50']">
        <div :class="['w-full', containerClass]">
            <div class="flex justify-between h-20 items-center">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <Link href="/" class="text-2xl font-extrabold tracking-tighter uppercase text-[#1866ed]">
                        <img :src="logo1" alt="Logo" style="width: 150px;">
                    </Link>
                </div>

                <!-- Center Content Slot (e.g., Compact Search) -->
                <slot name="center" />

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6 shrink-0">

                    <Link :href="route('list-spot')"
                        class="hidden lg:block text-[15px] font-bold text-[#1866ed] hover:text-blue-800 transition">List
                        your spot</Link>

                    <div v-if="canLogin"
                        class="ml-4 flex items-center space-x-4 border-l pl-6 border-gray-200 whitespace-nowrap">
                        <template v-if="$page.props.auth?.user">
                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-[15px] font-semibold text-gray-900 transition duration-150 ease-in-out hover:text-[#1866ed] focus:outline-none">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('bookings.history')">
                                            My Bookings
                                        </DropdownLink>
                                        <DropdownLink :href="route('spots.my-listings')">
                                            My Listings
                                        </DropdownLink>
                                        <DropdownLink :href="route('profile.edit')">
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </template>
                        <template v-else>
                            <Link :href="route('login')"
                                class="text-[15px] font-semibold text-gray-900 hover:text-[#1866ed] transition flex items-center">
                                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                                Log In or Sign Up
                            </Link>
                        </template>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden ml-auto">
                    <button @click="showingMobileMenu = !showingMobileMenu" type="button"
                        class="text-gray-500 hover:text-gray-700 focus:outline-none p-2 rounded-md hover:bg-gray-100 transition">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path v-if="!showingMobileMenu" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div v-show="showingMobileMenu" class="md:hidden border-t border-gray-100 bg-white">
            <div class="pt-2 pb-3 space-y-1">
                <ResponsiveNavLink :href="route('home')" :active="route().current('home')">
                    Home
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('list-spot')" :active="route().current('list-spot')">
                    List your spot
                </ResponsiveNavLink>
            </div>

            <!-- Mobile Auth Links -->
            <div class="pt-4 pb-1 border-t border-gray-200">
                <template v-if="$page.props.auth?.user">
                    <div class="px-4 mb-3">
                        <div class="text-base font-bold text-gray-900">{{ $page.props.auth.user.name }}</div>
                        <div class="text-sm font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
                    </div>
                    <div class="space-y-1">
                        <ResponsiveNavLink :href="route('bookings.history')"
                            :active="route().current('bookings.history')">
                            My Bookings
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('spots.my-listings')"
                            :active="route().current('spots.my-listings')">
                            My Listings
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('profile.edit')" :active="route().current('profile.edit')">
                            Profile
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="w-full text-left">
                            Log Out
                        </ResponsiveNavLink>
                    </div>
                </template>
                <template v-else>
                    <div class="space-y-1">
                        <ResponsiveNavLink :href="route('login')">
                            Log In
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('register')">
                            Sign Up
                        </ResponsiveNavLink>
                    </div>
                </template>
            </div>
        </div>
    </nav>
</template>
