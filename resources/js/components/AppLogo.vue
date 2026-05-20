<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface AppSettings {
    name: string;
    logo: string | null;
    favicon: string | null;
    theme: string;
}

const page = usePage();
const appSettings = computed(() => page.props.appSettings as AppSettings | undefined);
const appName = computed(() => appSettings.value?.name || page.props.name || 'Laravel Starter Kit');
const appLogo = computed(() => appSettings.value?.logo || null);
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground overflow-hidden"
    >
        <img v-if="appLogo" :src="appLogo" alt="Logo" class="size-full object-cover" />
        <AppLogoIcon v-else class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold text-neutral-800 dark:text-neutral-200">
            {{ appName }}
        </span>
    </div>
</template>
