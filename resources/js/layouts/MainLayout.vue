<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';

interface BreadcrumbItem {
    title: string;
    href?: string;
}

withDefaults(defineProps<{
    breadcrumbs?: BreadcrumbItem[];
    showBreadcrumbs?: boolean;
}>(), {
    showBreadcrumbs: true
});
</script>

<template>
    <div class="w-full min-w-full min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <div class="p-4 md:p-8">
            <div class="max-w-7xl mx-auto">
                <NavBar />
                
                <!-- Breadcrumbs -->
                <div v-if="breadcrumbs && breadcrumbs.length > 0" class="mb-4 sm:mb-6 px-2 sm:px-4 py-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </div>
                
                <slot />
            </div>
        </div>
    </div>
</template>
