<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import CurvexIcon from '@/icons/CurvexIcon.vue';
import ThemeToggle from './ThemeToggle.vue';

</script>

<template>
        <!-- Navbar -->
        <nav class="w-full flex items-center justify-between mb-6 sm:mb-8px-2 sm:px-4">
        <Link href="/" class="flex items-center gap-2 sm:gap-3">
            <CurvexIcon class="w-8 h-8 sm:w-10 sm:h-10" />
            <span class="text-xl sm:text-2xl font-extrabold tracking-tight">Curvex</span>
        </Link>
        <ThemeToggle />
        </nav>
</template>