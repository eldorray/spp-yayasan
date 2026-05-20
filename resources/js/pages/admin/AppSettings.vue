<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { 
    AppWindow, 
    Check, 
    Globe, 
    Image as ImageIcon, 
    RotateCcw, 
    Save, 
    Settings, 
    Sparkles, 
    Upload 
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

type Props = {
    settings: {
        app_name: string;
        app_logo_url: string | null;
        app_favicon_url: string | null;
        app_theme: string;
    };
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin' },
            { title: 'App Settings', href: '/admin/settings' },
        ],
    },
});

const form = useForm({
    app_name: props.settings.app_name,
    app_theme: props.settings.app_theme,
    app_logo: null as File | null,
    app_favicon: null as File | null,
    reset: false,
});

const logoPreview = ref<string | null>(props.settings.app_logo_url);
const faviconPreview = ref<string | null>(props.settings.app_favicon_url);

const onLogoChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.app_logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const onFaviconChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.app_favicon = file;
        faviconPreview.value = URL.createObjectURL(file);
    }
};

const themes = [
    {
        id: 'tahoe-slate',
        name: 'Tahoe Slate',
        description: 'Charcoal & Slate',
        class: 'bg-neutral-800 dark:bg-neutral-200',
        previewColors: ['#171717', '#404040', '#737373'],
    },
    {
        id: 'tahoe-blue',
        name: 'Tahoe Blue',
        description: 'Classic Apple Blue',
        class: 'bg-blue-600',
        previewColors: ['#0066cc', '#3399ff', '#93c5fd'],
    },
    {
        id: 'emerald-garden',
        name: 'Emerald Garden',
        description: 'Mint & Emerald',
        class: 'bg-emerald-600',
        previewColors: ['#059669', '#34d399', '#a7f3d0'],
    },
    {
        id: 'sunset-rose',
        name: 'Sunset Rose',
        description: 'Vibrant Pink',
        class: 'bg-rose-600',
        previewColors: ['#db2777', '#f472b6', '#fbcfe8'],
    },
    {
        id: 'royal-indigo',
        name: 'Royal Indigo',
        description: 'Premium Purple',
        class: 'bg-indigo-600',
        previewColors: ['#4f46e5', '#818cf8', '#c7d2fe'],
    },
];

const submit = () => {
    form.reset = false;
    form.post('/admin/settings', {
        preserveScroll: true,
    });
};

const resetToDefaults = () => {
    if (confirm('Are you sure you want to reset all settings to defaults? This will erase custom logo, favicon and theme.')) {
        form.reset = true;
        form.post('/admin/settings', {
            onSuccess: () => {
                logoPreview.value = null;
                faviconPreview.value = null;
                form.app_name = 'Laravel';
                form.app_theme = 'tahoe-slate';
            },
        });
    }
};
</script>

<template>
    <Head title="App Settings - Admin" />

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col gap-1">
            <h1 class="text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">Application Settings</h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Customize your application name, branding logos, favicon, and color theme.
            </p>
        </div>

        <!-- Tahoe macOS Window -->
        <div class="tahoe-window max-w-4xl">
            <!-- Window Title Bar -->
            <div class="tahoe-title-bar">
                <div class="flex items-center gap-2">
                    <!-- Traffic Lights -->
                    <div class="flex gap-1.5">
                        <span class="h-3 w-3 rounded-full bg-[#FF5F56] border border-[#E0443E]"></span>
                        <span class="h-3 w-3 rounded-full bg-[#FFBD2E] border border-[#DEA123]"></span>
                        <span class="h-3 w-3 rounded-full bg-[#27C93F] border border-[#1AAB29]"></span>
                    </div>
                    <span class="ml-2 text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">Preferences</span>
                </div>
                <div class="text-sm font-semibold text-neutral-700 dark:text-neutral-200 flex items-center gap-1.5">
                    <Settings class="h-3.5 w-3.5" /> App Settings
                </div>
                <div></div>
            </div>

            <!-- Settings Form -->
            <form @submit.prevent="submit" class="p-8 space-y-8 divide-y divide-neutral-200/40 dark:divide-neutral-800/40">
                <!-- Section 1: General Branding -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-8">
                    <div>
                        <h3 class="text-sm font-bold text-neutral-800 dark:text-neutral-200 flex items-center gap-2">
                            <Globe class="h-4 w-4 text-neutral-500" /> App Identity
                        </h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                            Define the public name and core metadata of your school system portal.
                        </p>
                    </div>
                    <div class="md:col-span-2 space-y-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="app_name" class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">Application Name</label>
                            <input
                                id="app_name"
                                v-model="form.app_name"
                                type="text"
                                placeholder="Enter application name"
                                class="tahoe-input w-full md:max-w-md"
                                required
                            />
                            <span v-if="form.errors.app_name" class="text-xs text-red-500 font-medium">
                                {{ form.errors.app_name }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Logo and Favicon Files -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 py-8">
                    <div>
                        <h3 class="text-sm font-bold text-neutral-800 dark:text-neutral-200 flex items-center gap-2">
                            <ImageIcon class="h-4 w-4 text-neutral-500" /> Visual Assets
                        </h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                            Upload your system logos and browser icons. Keep files lightweight for faster load times.
                        </p>
                    </div>
                    <div class="md:col-span-2 space-y-8">
                        <!-- App Logo Upload -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                            <!-- Squircle Logo Frame Preview -->
                            <div class="relative flex aspect-square size-16 items-center justify-center rounded-2xl bg-neutral-100 dark:bg-zinc-800 border border-neutral-200/55 dark:border-zinc-700/60 shadow-inner overflow-hidden shrink-0">
                                <img v-if="logoPreview" :src="logoPreview" alt="Logo Preview" class="size-full object-cover" />
                                <div v-else class="flex flex-col items-center justify-center text-neutral-400 dark:text-neutral-600">
                                    <ImageIcon class="h-6 w-6" />
                                    <span class="text-[9px] uppercase tracking-wider font-semibold mt-1">No Logo</span>
                                </div>
                            </div>
                            <div class="space-y-2 w-full">
                                <span class="text-xs font-semibold text-neutral-600 dark:text-neutral-400 block">Application Logo</span>
                                <div class="flex items-center gap-2">
                                    <label class="tahoe-button-secondary cursor-pointer flex items-center gap-1.5 text-xs">
                                        <Upload class="h-3.5 w-3.5" /> Choose Logo
                                        <input type="file" class="hidden" accept="image/png,image/jpg,image/jpeg,image/svg+xml" @change="onLogoChange" />
                                    </label>
                                    <span class="text-[10px] text-neutral-400 dark:text-neutral-500">Supports PNG, JPG, SVG (Max 2MB)</span>
                                </div>
                                <span v-if="form.errors.app_logo" class="text-xs text-red-500 font-medium block">
                                    {{ form.errors.app_logo }}
                                </span>
                            </div>
                        </div>

                        <!-- App Favicon Upload -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                            <!-- Simulated Web Browser Tab Preview -->
                            <div class="w-full sm:max-w-[240px] rounded-lg border border-neutral-200 dark:border-zinc-800 overflow-hidden shadow-xs shrink-0 bg-neutral-100 dark:bg-zinc-950">
                                <div class="bg-neutral-200/70 dark:bg-zinc-900 border-b border-neutral-300/40 dark:border-zinc-800 p-2 flex items-center gap-2">
                                    <!-- Browser Control dots -->
                                    <div class="flex gap-1">
                                        <span class="h-1.5 w-1.5 rounded-full bg-neutral-400/70"></span>
                                        <span class="h-1.5 w-1.5 rounded-full bg-neutral-400/70"></span>
                                        <span class="h-1.5 w-1.5 rounded-full bg-neutral-400/70"></span>
                                    </div>
                                    <!-- Simulated Tab -->
                                    <div class="bg-white dark:bg-zinc-850 rounded px-2 py-0.5 flex items-center gap-1 text-[10px] text-neutral-600 dark:text-neutral-300 w-36 shadow-xs border border-neutral-200/50 dark:border-zinc-800">
                                        <img v-if="faviconPreview" :src="faviconPreview" class="h-3.5 w-3.5 object-contain rounded" />
                                        <div v-else class="h-3.5 w-3.5 rounded-full bg-neutral-300 dark:bg-zinc-700 shrink-0"></div>
                                        <span class="truncate font-semibold max-w-[90px]">{{ form.app_name || 'Portal Sekolah' }}</span>
                                    </div>
                                </div>
                                <div class="h-5 bg-white dark:bg-zinc-900"></div>
                            </div>

                            <div class="space-y-2 w-full">
                                <span class="text-xs font-semibold text-neutral-600 dark:text-neutral-400 block">Browser Favicon</span>
                                <div class="flex items-center gap-2">
                                    <label class="tahoe-button-secondary cursor-pointer flex items-center gap-1.5 text-xs">
                                        <Upload class="h-3.5 w-3.5" /> Choose Favicon
                                        <input type="file" class="hidden" accept="image/png,image/x-icon,image/svg+xml" @change="onFaviconChange" />
                                    </label>
                                    <span class="text-[10px] text-neutral-400 dark:text-neutral-500">Supports ICO, PNG, SVG (Max 1MB)</span>
                                </div>
                                <span v-if="form.errors.app_favicon" class="text-xs text-red-500 font-medium block">
                                    {{ form.errors.app_favicon }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Color Theme Selection -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 py-8">
                    <div>
                        <h3 class="text-sm font-bold text-neutral-800 dark:text-neutral-200 flex items-center gap-2">
                            <Sparkles class="h-4 w-4 text-neutral-500" /> Color Accent Theme
                        </h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                            Select the primary accent color scheme. This color will be applied dynamically to links, active buttons, and visual markers.
                        </p>
                    </div>
                    <div class="md:col-span-2">
                        <!-- Theme Cards Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            <div 
                                v-for="theme in themes" 
                                :key="theme.id"
                                @click="form.app_theme = theme.id"
                                :class="[
                                    'cursor-pointer rounded-xl border p-4 flex flex-col justify-between transition-all duration-300 relative group overflow-hidden',
                                    form.app_theme === theme.id 
                                        ? 'border-neutral-700 dark:border-neutral-300 ring-2 ring-neutral-700/10 bg-neutral-50/50 dark:bg-zinc-800/40 shadow-sm' 
                                        : 'border-neutral-200/50 dark:border-zinc-800 hover:border-neutral-350 dark:hover:border-zinc-700 hover:bg-neutral-50/20 dark:hover:bg-zinc-900/10'
                                ]"
                            >
                                <!-- Theme Selection Tick -->
                                <div 
                                    v-if="form.app_theme === theme.id"
                                    class="absolute top-2 right-2 flex size-5 items-center justify-center rounded-full bg-neutral-800 dark:bg-white text-white dark:text-neutral-900 scale-100 transition-transform"
                                >
                                    <Check class="h-3 w-3 stroke-[3px]" />
                                </div>

                                <!-- Theme Miniature Interface Preview Mockup -->
                                <div class="rounded-lg bg-white dark:bg-zinc-950 border border-neutral-200/80 dark:border-zinc-800/80 p-2 space-y-2 mb-4">
                                    <!-- mini sidebar mockup -->
                                    <div class="flex gap-2">
                                        <div class="w-1/3 bg-neutral-100 dark:bg-zinc-900 rounded p-1 space-y-1">
                                            <!-- mini app icon -->
                                            <div class="flex items-center gap-1">
                                                <div class="h-2 w-2 rounded-sm" :style="{ backgroundColor: theme.previewColors[0] }"></div>
                                                <div class="h-1 w-4 bg-neutral-300 dark:bg-zinc-700 rounded-sm"></div>
                                            </div>
                                            <!-- active sidebar item -->
                                            <div class="h-2.5 rounded-sm flex items-center px-1" :style="{ backgroundColor: theme.previewColors[2] + '30' }">
                                                <div class="h-1 w-full rounded-sm" :style="{ backgroundColor: theme.previewColors[0] }"></div>
                                            </div>
                                            <!-- other item -->
                                            <div class="h-1 w-2/3 bg-neutral-200 dark:bg-zinc-700 rounded-sm"></div>
                                        </div>
                                        <div class="flex-1 space-y-1.5 p-1">
                                            <div class="h-2 w-1/2 bg-neutral-200 dark:bg-zinc-700 rounded-sm"></div>
                                            <!-- mini cta button -->
                                            <div class="h-3 rounded-sm flex items-center justify-center" :style="{ backgroundColor: theme.previewColors[0] }">
                                                <span class="text-[5px] text-white font-bold leading-none uppercase">Go</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-xs font-bold text-neutral-800 dark:text-neutral-200">{{ theme.name }}</h4>
                                    <p class="text-[10px] text-neutral-400 dark:text-neutral-500 mt-0.5">{{ theme.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6">
                    <button
                        type="button"
                        class="tahoe-button-secondary border-red-200/50 hover:bg-red-50 hover:text-red-600 dark:border-red-950/20 dark:hover:bg-red-950/20 text-neutral-500 flex items-center gap-1.5 text-xs cursor-pointer order-2 sm:order-1"
                        @click="resetToDefaults"
                        :disabled="form.processing"
                    >
                        <RotateCcw class="h-3.5 w-3.5" /> Reset to System Defaults
                    </button>

                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end order-1 sm:order-2">
                        <button
                            type="submit"
                            class="tahoe-button-primary flex items-center justify-center gap-1.5 w-full sm:w-auto cursor-pointer"
                            :disabled="form.processing"
                        >
                            <Save class="h-3.5 w-3.5" />
                            <span>{{ form.processing ? 'Saving...' : 'Save Changes' }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
