<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { dashboard, login, register } from '@/routes';
import { useAppearance } from '@/composables/useAppearance';
import { Motion } from '@motionone/vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const page = usePage();
const { resolvedAppearance, updateAppearance } = useAppearance();

// Theme toggle
const toggleTheme = () => {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
};

// Dynamic branding from App Settings
const appSettings = computed(() => page.props.appSettings as { name: string; logo: string | null; theme?: string } | undefined);
const appName = computed(() => appSettings.value?.name || 'SPP Yayasan');
const appLogo = computed(() => appSettings.value?.logo || null);

const dashboardUrl = computed(() =>
    page.props.currentTeam ? dashboard(page.props.currentTeam.slug).url : '/dashboard',
);

// Tab selection state for Interactive Dashboard Preview
const activeTab = ref<'overview' | 'transactions' | 'ai'>('overview');

// Mock Data for Live Transactions Feed
const recentTransactions = [
    {
        id: 1,
        student: 'Aisyah Putri',
        grade: 'MI IT - Kelas 3A',
        type: 'SPP Mei 2026',
        amount: 250000,
        time: '2 menit yang lalu',
        initials: 'AP',
        bgColor: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
    },
    {
        id: 2,
        student: 'Rizky Ramadhan',
        grade: 'SMP IT - Kelas 8B',
        type: 'SPP Mei + Uang Kegiatan',
        amount: 550000,
        time: '15 menit yang lalu',
        initials: 'RR',
        bgColor: 'bg-blue-500/10 text-blue-600 dark:text-blue-400'
    },
    {
        id: 3,
        student: 'Amanda Kartika',
        grade: 'MI IT - Kelas 6B',
        type: 'SPP Mei 2026',
        amount: 250000,
        time: '1 jam yang lalu',
        initials: 'AK',
        bgColor: 'bg-amber-500/10 text-amber-600 dark:text-amber-400'
    },
    {
        id: 4,
        student: 'Fahri Hamzah',
        grade: 'SMP IT - Kelas 9A',
        type: 'SPP Mei 2026',
        amount: 300000,
        time: '2 jam yang lalu',
        initials: 'FH',
        bgColor: 'bg-purple-500/10 text-purple-600 dark:text-purple-400'
    }
];

// Format Rupiah function
const formatRupiah = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
};

// Handle smooth scroll to preview
const scrollToPreview = () => {
    document.getElementById('dashboard-preview')?.scrollIntoView({ behavior: 'smooth' });
};
</script>

<template>
    <Head title="Selamat Datang - Portal Keuangan & SPP Yayasan">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
        <meta name="description" content="Portal Administrasi Keuangan Terintegrasi untuk Lembaga Pendidikan Yayasan. Kelola pembayaran SPP bulanan, uang gedung, kegiatan, dan laporan kas secara transparan dan real-time." />
    </Head>

    <div class="relative min-h-screen bg-neutral-50 text-neutral-900 transition-colors duration-300 dark:bg-[#09090b] dark:text-neutral-100 overflow-x-hidden font-sans selection:bg-primary/20 selection:text-primary">
        
        <!-- Strict Private Access & Non-Commercial Notice Banner -->
        <div class="relative z-50 bg-amber-500/10 dark:bg-amber-500/5 border-b border-amber-500/20 px-4 py-2.5 text-center text-xs text-amber-750 dark:text-amber-400 font-semibold tracking-wide flex items-center justify-center gap-2 select-none">
            <span class="flex h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
            🔐 SISTEM INTERNAL PRIVAT &bull; KHUSUS PENGGUNAAN PRIBADI YAYASAN DARUL HALIM MADANI &bull; TIDAK UNTUK DIJUAL / NON-KOMERSIAL
        </div>

        <!-- Elegant Background Glow Orbs -->
        <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-primary/10 blur-[120px] pointer-events-none dark:bg-primary/5"></div>
        <div class="absolute top-[40%] right-[-10%] w-[600px] h-[600px] rounded-full bg-amber-500/10 blur-[130px] pointer-events-none dark:bg-amber-500/5"></div>
        
        <!-- Floating Glass Header -->
        <header class="sticky top-0 z-50 w-full px-4 py-4 md:px-8 max-w-7xl mx-auto">
            <nav class="flex items-center justify-between px-6 py-3 border border-neutral-200/40 dark:border-neutral-800/40 bg-white/70 dark:bg-[#121214]/70 backdrop-blur-xl rounded-full shadow-[0_4px_30px_rgba(0,0,0,0.03)] dark:shadow-[0_4px_30px_rgba(0,0,0,0.2)]">
                <!-- Branding / Logo -->
                <div class="flex items-center gap-3">
                    <div class="flex aspect-square size-9 items-center justify-center rounded-xl bg-primary text-primary-foreground overflow-hidden shadow-md">
                        <img v-if="appLogo" :src="appLogo" alt="Logo" class="size-full object-cover" />
                        <svg v-else class="size-5 fill-current text-white dark:text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M12 2L2 22h20L12 2zm0 3.99L19.53 19H4.47L12 5.99zM11 16h2v2h-2zm0-6h2v4h-2z" />
                        </svg>
                    </div>
                    <span class="text-md font-semibold tracking-tight text-neutral-800 dark:text-neutral-100 uppercase">
                        {{ appName }}
                    </span>
                </div>

                <!-- Navigation Action Links -->
                <div class="flex items-center gap-3">
                    <!-- Light/Dark Toggle -->
                    <button 
                        id="theme-toggle"
                        @click="toggleTheme" 
                        class="p-2.5 rounded-full border border-neutral-200/40 dark:border-neutral-800/40 bg-neutral-100/50 hover:bg-neutral-200/50 dark:bg-neutral-800/50 dark:hover:bg-neutral-700/50 text-neutral-600 dark:text-neutral-400 transition-all hover:scale-105 active:scale-95"
                        title="Ubah Tema"
                    >
                        <svg v-if="resolvedAppearance === 'dark'" class="w-4 h-4 fill-current" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464-5.636a1 1 0 010 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-5.636 4.464a1 1 0 011.414 0l.707.707a1 1 0 01-1.414 1.414l-.707-.707a1 1 0 010-1.414zM7 16a1 1 0 100-2H6a1 1 0 100 2h1zm-.464-11.464a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM3 10a1 1 0 011-1h1a1 1 0 110 2H4a1 1 0 01-1-1z" />
                        </svg>
                        <svg v-else class="w-4 h-4 fill-current" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                        </svg>
                    </button>

                    <div class="h-6 w-px bg-neutral-200 dark:bg-neutral-800"></div>

                    <!-- Auth Options -->
                    <Link
                        v-if="$page.props.auth.user"
                        id="btn-dashboard"
                        :href="dashboardUrl"
                        class="px-5 py-2 text-xs font-semibold text-white bg-primary hover:bg-primary/95 dark:text-neutral-900 dark:bg-white dark:hover:bg-neutral-100 rounded-full transition-all shadow-md active:scale-95"
                    >
                        Masuk Dashboard
                    </Link>
                    <Link
                        v-else
                        id="btn-login"
                        :href="login()"
                        class="px-5 py-2 text-xs font-semibold text-white bg-primary hover:bg-primary/95 dark:text-neutral-900 dark:bg-white dark:hover:bg-neutral-100 rounded-full transition-all shadow-md active:scale-95"
                    >
                        Masuk Portal
                    </Link>
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <main class="max-w-7xl mx-auto px-4 py-8 md:px-8 md:py-16">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Column: Title and Descriptions -->
                <div class="lg:col-span-6 space-y-6 text-left">
                    <!-- Staggered Entry using Motion One -->
                    <Motion 
                        tag="div"
                        :initial="{ opacity: 0, y: -12 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.6, easing: [0.16, 1, 0.3, 1] }"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-amber-500/20 bg-amber-500/5 text-amber-600 dark:text-amber-400 text-xs font-semibold tracking-wide uppercase"
                    >
                        <span class="flex h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Aplikasi Penggunaan Pribadi (Non-Komersial)
                    </Motion>
                    
                    <Motion 
                        tag="h1"
                        :initial="{ opacity: 0, y: 18 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.12, duration: 0.75, easing: [0.16, 1, 0.3, 1] }"
                        class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight"
                    >
                        Tata Kelola SPP & Keuangan
                        <span class="bg-gradient-to-r from-primary to-amber-500 bg-clip-text text-transparent block mt-1">
                            YPD Darul Halim Madani
                        </span>
                    </Motion>
                    
                    <Motion 
                        tag="p"
                        :initial="{ opacity: 0, y: 18 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.22, duration: 0.75, easing: [0.16, 1, 0.3, 1] }"
                        class="text-neutral-600 dark:text-neutral-400 text-lg leading-relaxed max-w-xl"
                    >
                        Sistem informasi keuangan internal yang dikonfigurasi secara mandiri (Self-Hosted) untuk pengelolaan dana pendidikan, pencatatan transaksi SPP, serta pelaporan kas unit MI dan SMP Terpadu di bawah naungan Yayasan.
                    </Motion>

                    <!-- Interactive Buttons with Spring Transitions -->
                    <Motion 
                        tag="div"
                        :initial="{ opacity: 0, y: 18 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.32, duration: 0.75, easing: [0.16, 1, 0.3, 1] }"
                        class="flex flex-col sm:flex-row gap-4 pt-2"
                    >
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="login()"
                            class="flex items-center justify-center gap-2 px-6 py-3.5 text-sm font-semibold rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all hover:translate-y-[-2px] active:translate-y-[0]"
                        >
                            Masuk ke Portal Keuangan
                            <svg class="w-4 h-4 stroke-current fill-none" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                        <Link
                            v-else
                            :href="dashboardUrl"
                            class="flex items-center justify-center gap-2 px-6 py-3.5 text-sm font-semibold rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all hover:translate-y-[-2px] active:translate-y-[0]"
                        >
                            Buka Dashboard Utama
                            <svg class="w-4 h-4 stroke-current fill-none" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>

                        <button
                            id="hero-cta-preview"
                            @click="scrollToPreview"
                            class="flex items-center justify-center gap-2 px-6 py-3.5 text-sm font-semibold rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white/50 hover:bg-white dark:bg-neutral-900/50 dark:hover:bg-neutral-900 transition-all hover:translate-y-[-2px] active:translate-y-[0]"
                        >
                            <svg class="w-4 h-4 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat Ringkasan Sistem
                        </button>
                    </Motion>

                    <!-- Micro-facts grid -->
                    <Motion 
                        tag="div"
                        :initial="{ opacity: 0, y: 18 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.42, duration: 0.75, easing: [0.16, 1, 0.3, 1] }"
                        class="grid grid-cols-3 gap-4 pt-6 border-t border-neutral-200/50 dark:border-neutral-800/50 max-w-md"
                    >
                        <div>
                            <p class="text-2xl font-bold text-primary">2 Unit</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-500">Instansi (MI & SMP)</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-amber-500">Terenkripsi</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-500">Keamanan Akses</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-emerald-500">Sentral</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-500">Database Real-Time</p>
                        </div>
                    </Motion>
                </div>

                <!-- Right Column: Interactive macOS Tahoe Premium Dashboard Mockup with Spring Entrance -->
                <Motion 
                    tag="div"
                    :initial="{ opacity: 0, scale: 0.95, y: 30 }"
                    :animate="{ opacity: 1, scale: 1, y: 0 }"
                    :transition="{ delay: 0.2, type: 'spring', stiffness: 100, damping: 16 }"
                    class="lg:col-span-6 w-full max-w-xl mx-auto" 
                    id="dashboard-preview"
                >
                    <div class="tahoe-window relative">
                        
                        <!-- macOS Title Bar Style -->
                        <div class="tahoe-title-bar">
                            <div class="flex items-center gap-1.5">
                                <span class="h-3 w-3 rounded-full bg-[#FF5F56] border border-[#E0443E] block"></span>
                                <span class="h-3 w-3 rounded-full bg-[#FFBD2E] border border-[#DEA123] block"></span>
                                <span class="h-3 w-3 rounded-full bg-[#27C93F] border border-[#1AAB29] block"></span>
                            </div>
                            <span class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 select-none">
                                Portal Keuangan Yayasan - Live Preview
                            </span>
                            <div class="w-12"></div>
                        </div>

                        <!-- Mini Mock Navigation Tab Selector inside Window -->
                        <div class="flex items-center gap-1.5 px-4 py-2 bg-neutral-100/55 dark:bg-neutral-800/55 border-b border-neutral-200/40 dark:border-neutral-800/40">
                            <button
                                @click="activeTab = 'overview'"
                                :class="[
                                    'px-3 py-1.5 text-[11px] font-semibold rounded-md transition-all flex items-center gap-1.5',
                                    activeTab === 'overview'
                                        ? 'bg-white text-neutral-900 shadow-xs dark:bg-neutral-700 dark:text-white'
                                        : 'text-neutral-500 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200'
                                ]"
                            >
                                <svg class="w-3.5 h-3.5 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                </svg>
                                Ikhtisar Keuangan
                            </button>
                            <button
                                @click="activeTab = 'transactions'"
                                :class="[
                                    'px-3 py-1.5 text-[11px] font-semibold rounded-md transition-all flex items-center gap-1.5',
                                    activeTab === 'transactions'
                                        ? 'bg-white text-neutral-900 shadow-xs dark:bg-neutral-700 dark:text-white'
                                        : 'text-neutral-500 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200'
                                ]"
                            >
                                <svg class="w-3.5 h-3.5 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                Transaksi Live
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                            </button>
                            <button
                                @click="activeTab = 'ai'"
                                :class="[
                                    'px-3 py-1.5 text-[11px] font-semibold rounded-md transition-all flex items-center gap-1.5',
                                    activeTab === 'ai'
                                        ? 'bg-white text-neutral-900 shadow-xs dark:bg-neutral-700 dark:text-white'
                                        : 'text-neutral-500 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200'
                                ]"
                            >
                                <svg class="w-3.5 h-3.5 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                Analisis AI
                            </button>
                        </div>

                        <!-- Window Content Panels with Dynamic Cross-Fades using Motion One -->
                        <div class="p-5 min-h-[350px] flex flex-col justify-between overflow-hidden">
                            
                            <!-- TAB 1: OVERVIEW PANEL -->
                            <Motion 
                                v-if="activeTab === 'overview'" 
                                key="overview"
                                :initial="{ opacity: 0, y: 10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ duration: 0.35, easing: 'ease-out' }"
                                class="space-y-5"
                            >
                                <!-- KPI Cards Row -->
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="p-3 bg-neutral-100/50 dark:bg-neutral-800/40 border border-neutral-200/20 rounded-xl space-y-1">
                                        <p class="text-[10px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Penerimaan SPP</p>
                                        <p class="text-sm font-bold text-neutral-800 dark:text-neutral-200 truncate">482,5jt</p>
                                        <span class="inline-flex items-center text-[9px] font-bold text-emerald-500 bg-emerald-500/10 px-1.5 py-0.5 rounded">
                                            +12.4%
                                        </span>
                                    </div>
                                    <div class="p-3 bg-neutral-100/50 dark:bg-neutral-800/40 border border-neutral-200/20 rounded-xl space-y-1">
                                        <p class="text-[10px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Kepatuhan SPP</p>
                                        <p class="text-sm font-bold text-neutral-800 dark:text-neutral-200">94.8%</p>
                                        <!-- Mini horizontal progress bar -->
                                        <div class="w-full h-1.5 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary rounded-full" style="width: 94.8%"></div>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-neutral-100/50 dark:bg-neutral-800/40 border border-neutral-200/20 rounded-xl space-y-1">
                                        <p class="text-[10px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Kas Yayasan</p>
                                        <p class="text-sm font-bold text-neutral-800 dark:text-neutral-200">1,28 M</p>
                                        <span class="inline-flex items-center text-[9px] font-bold text-primary bg-primary/10 px-1.5 py-0.5 rounded">
                                            Stabil
                                        </span>
                                    </div>
                                </div>

                                <!-- Custom Area Line Chart using SVG (Highly Elegant) -->
                                <div class="relative p-4 bg-neutral-50/50 dark:bg-neutral-900/40 border border-neutral-200/20 rounded-2xl">
                                    <p class="text-[10px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider mb-3">Tren Penerimaan Kas (6 Bulan Terakhir)</p>
                                    
                                    <div class="h-32 w-full">
                                        <svg class="w-full h-full overflow-visible" viewBox="0 0 500 120" preserveAspectRatio="none">
                                            <!-- Chart Gradient Definition -->
                                            <defs>
                                                <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%" stop-color="var(--primary)" stop-opacity="0.25" />
                                                    <stop offset="100%" stop-color="var(--primary)" stop-opacity="0" />
                                                </linearGradient>
                                            </defs>
                                            
                                            <!-- Grid lines -->
                                            <line x1="0" y1="30" x2="500" y2="30" stroke="rgba(150,150,150,0.1)" stroke-dasharray="3" />
                                            <line x1="0" y1="60" x2="500" y2="60" stroke="rgba(150,150,150,0.1)" stroke-dasharray="3" />
                                            <line x1="0" y1="90" x2="500" y2="90" stroke="rgba(150,150,150,0.1)" stroke-dasharray="3" />
                                            
                                            <!-- Dynamic curved path area -->
                                            <path d="M 0 100 Q 80 85 100 95 T 200 45 T 300 65 T 400 35 T 500 20 L 500 120 L 0 120 Z" fill="url(#chartGradient)" />
                                            
                                            <!-- Dynamic curved stroke line -->
                                            <path d="M 0 100 Q 80 85 100 95 T 200 45 T 300 65 T 400 35 T 500 20" fill="none" stroke="var(--primary)" stroke-width="3" stroke-linecap="round" />
                                            
                                            <!-- Points of interest -->
                                            <circle cx="200" cy="45" r="4.5" fill="var(--primary)" stroke="white" stroke-width="1.5" />
                                            <circle cx="500" cy="20" r="4.5" fill="var(--primary)" stroke="white" stroke-width="1.5" />
                                        </svg>
                                    </div>
                                    
                                    <!-- Chart X Axis labels -->
                                    <div class="flex justify-between items-center text-[9px] text-neutral-400 font-bold uppercase tracking-wider mt-1 px-1">
                                        <span>Nov</span>
                                        <span>Des</span>
                                        <span>Jan</span>
                                        <span>Feb</span>
                                        <span>Mar</span>
                                        <span class="text-primary">Apr</span>
                                    </div>
                                </div>
                            </Motion>

                            <!-- TAB 2: LIVE TRANSACTIONS PANEL -->
                            <Motion 
                                v-if="activeTab === 'transactions'" 
                                key="transactions"
                                :initial="{ opacity: 0, y: 10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ duration: 0.35, easing: 'ease-out' }"
                                class="space-y-3.5"
                            >
                                <div class="flex items-center justify-between border-b border-neutral-200/30 pb-2">
                                    <p class="text-[10px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Aktivitas Pembayaran SPP Masuk</p>
                                    <span class="flex items-center gap-1 text-[9px] font-bold text-emerald-500 bg-emerald-500/10 px-2 py-0.5 rounded-full uppercase tracking-wider">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                        Terhubung
                                    </span>
                                </div>

                                <!-- Transaction list loop -->
                                <div class="space-y-2.5 max-h-[250px] overflow-y-auto pr-1">
                                    <div 
                                        v-for="tx in recentTransactions" 
                                        :key="tx.id"
                                        class="flex items-center justify-between p-2.5 rounded-xl bg-neutral-100/50 hover:bg-neutral-100 dark:bg-neutral-800/40 dark:hover:bg-neutral-800/70 border border-neutral-200/10 transition-all duration-200"
                                    >
                                        <div class="flex items-center gap-3">
                                            <!-- Initials Avatar -->
                                            <div :class="[tx.bgColor, 'flex aspect-square size-8 items-center justify-center rounded-lg font-bold text-xs shadow-xs']">
                                                {{ tx.initials }}
                                            </div>
                                            <div class="text-left space-y-0.5">
                                                <p class="text-xs font-bold text-neutral-800 dark:text-neutral-200">{{ tx.student }}</p>
                                                <p class="text-[9px] font-medium text-neutral-400 dark:text-neutral-500">{{ tx.grade }} &bull; {{ tx.type }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right space-y-0.5">
                                            <p class="text-xs font-bold text-emerald-500">{{ formatRupiah(tx.amount) }}</p>
                                            <p class="text-[9px] text-neutral-400">{{ tx.time }}</p>
                                        </div>
                                    </div>
                                </div>
                            </Motion>

                            <!-- TAB 3: AI ANALYSIS PANEL -->
                            <Motion 
                                v-if="activeTab === 'ai'" 
                                key="ai"
                                :initial="{ opacity: 0, y: 10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ duration: 0.35, easing: 'ease-out' }"
                                class="space-y-4"
                            >
                                <div class="flex items-center justify-between border-b border-neutral-200/30 pb-2">
                                    <p class="text-[10px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Asisten Finansial AI Terintegrasi</p>
                                    <span class="text-[9px] font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-full uppercase tracking-wider">
                                        Generative Insight
                                    </span>
                                </div>

                                <!-- Conversations Mockup -->
                                <div class="space-y-3 text-xs">
                                    <!-- User Bubble -->
                                    <div class="flex items-start gap-2.5 max-w-[85%]">
                                        <div class="size-6 rounded-md bg-neutral-200 dark:bg-neutral-800 flex items-center justify-center text-[10px] font-bold shrink-0">BD</div>
                                        <div class="p-2.5 rounded-2xl rounded-tl-none bg-neutral-100/70 dark:bg-neutral-800/40 border border-neutral-200/10 text-left leading-relaxed">
                                            Bagaimana status penagihan SPP SMP bulan ini dibandingkan dengan bulan kemarin?
                                        </div>
                                    </div>

                                    <!-- AI Bubble -->
                                    <div class="flex items-start gap-2.5 max-w-[90%] ml-auto flex-row-reverse">
                                        <div class="size-6 rounded-md bg-primary text-primary-foreground flex items-center justify-center text-[10px] font-bold shrink-0">AI</div>
                                        <div class="p-3 rounded-2xl rounded-tr-none bg-primary/5 border border-primary/20 text-left leading-relaxed space-y-2">
                                            <p>
                                                Tingkat kepatuhan SPP SMP bulan ini mencapai <strong class="text-primary">94.8%</strong>, mengalami kenaikan sebesar <strong class="text-emerald-500">+2.4%</strong> dibandingkan bulan lalu (<strong class="text-neutral-500">92.4%</strong>).
                                            </p>
                                            
                                            <!-- Miniature bar chart comparison -->
                                            <div class="flex items-end gap-6 pt-2 pb-1 px-4 border-t border-primary/10">
                                                <div class="space-y-1 text-center">
                                                    <div class="w-7 bg-neutral-300 dark:bg-neutral-700 rounded-t-sm" style="height: 48px"></div>
                                                    <span class="text-[8px] text-neutral-400 font-bold block">Mar (92.4%)</span>
                                                </div>
                                                <div class="space-y-1 text-center">
                                                    <div class="w-7 bg-primary rounded-t-sm shadow-xs" style="height: 60px"></div>
                                                    <span class="text-[8px] text-primary font-bold block">Apr (94.8%)</span>
                                                </div>
                                                <div class="text-[9px] text-neutral-500 dark:text-neutral-400 self-center pl-2 space-y-0.5">
                                                    <p class="font-bold text-neutral-700 dark:text-neutral-300">Rekomendasi AI:</p>
                                                    <p>Kirim pengingat WhatsApp otomatis ke 5.2% wali murid tersisa.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Motion>

                            <!-- Elegant Footer Info Inside Window Mockup -->
                            <div class="pt-3 border-t border-neutral-200/30 flex items-center justify-between text-[9px] text-neutral-400 dark:text-neutral-500 font-bold uppercase tracking-wider mt-4">
                                <span class="flex items-center gap-1">
                                    <span class="h-1.5 w-1.5 rounded-full bg-primary block"></span>
                                    Instansi Aktif: 2 Unit
                                </span>
                                <span>Akses Terenkripsi &bull; Database Internal</span>
                            </div>
                        </div>

                    </div>
                </Motion>

            </div>
        </main>

        <!-- Core Features Section -->
        <section class="max-w-7xl mx-auto px-4 py-16 md:px-8 border-t border-neutral-200/50 dark:border-neutral-800/50">
            <div class="text-center space-y-4 mb-16">
                <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                    Fokus Sistem Mandiri & Pribadi
                </h2>
                <p class="text-neutral-500 dark:text-neutral-400 max-w-xl mx-auto">
                    Seluruh fitur dalam aplikasi ini dibangun secara khusus untuk kebutuhan tata kelola operasional internal yayasan tanpa adanya fungsi komersial.
                </p>
            </div>

            <!-- Features Grid using Motion One Elastic Springs -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature Card 1 -->
                <Motion
                    tag="div"
                    :hover="{ y: -6, scale: 1.025 }"
                    :transition="{ type: 'spring', stiffness: 200, damping: 18 }"
                    class="tahoe-card space-y-4 group cursor-pointer"
                >
                    <div class="p-3 w-fit rounded-xl bg-primary/10 text-primary dark:bg-primary/20 transition-all group-hover:scale-110">
                        <svg class="w-6 h-6 stroke-current fill-none" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-800 dark:text-neutral-100">Integrasi MI & SMP Terpadu</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">
                        Penyatuan seluruh data operasional keuangan dan data siswa unit MI dan SMP secara sentralisasi dalam satu database dan panel kontrol terpadu.
                    </p>
                </Motion>

                <!-- Feature Card 2 -->
                <Motion
                    tag="div"
                    :hover="{ y: -6, scale: 1.025 }"
                    :transition="{ type: 'spring', stiffness: 200, damping: 18 }"
                    class="tahoe-card space-y-4 group cursor-pointer"
                >
                    <div class="p-3 w-fit rounded-xl bg-amber-500/10 text-amber-500 dark:bg-amber-500/20 transition-all group-hover:scale-110">
                        <svg class="w-6 h-6 stroke-current fill-none" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-800 dark:text-neutral-100">Pencatatan SPP Otomatis</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">
                        Pembuatan invoice tagihan bulanan siswa secara massal sesuai kriteria unit pendidikan, angkatan, serta pembebasan biaya bagi siswa kota Tangerang yang berhak.
                    </p>
                </Motion>

                <!-- Feature Card 3 -->
                <Motion
                    tag="div"
                    :hover="{ y: -6, scale: 1.025 }"
                    :transition="{ type: 'spring', stiffness: 200, damping: 18 }"
                    class="tahoe-card space-y-4 group cursor-pointer"
                >
                    <div class="p-3 w-fit rounded-xl bg-emerald-500/10 text-emerald-500 dark:bg-emerald-500/20 transition-all group-hover:scale-110">
                        <svg class="w-6 h-6 stroke-current fill-none" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-800 dark:text-neutral-100">Asisten AI Finansial Terintegrasi</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">
                        Konsultasi real-time sirkulasi saldo kas dan estimasi piutang menggunakan asisten kecerdasan buatan berbasis model Gemini 2.5 Flash yang beroperasi di lingkungan secure.
                    </p>
                </Motion>
            </div>
        </section>

        <!-- AI Assistant Banner Showcase Section -->
        <section class="max-w-7xl mx-auto px-4 pb-16 md:px-8">
            <Motion 
                tag="div"
                :hover="{ scale: 1.01 }"
                :transition="{ type: 'spring', stiffness: 180, damping: 15 }"
                class="relative overflow-hidden rounded-[24px] border border-neutral-200/40 dark:border-neutral-800/40 bg-gradient-to-r from-primary/5 via-amber-500/5 to-primary/5 p-8 md:p-12 text-center space-y-6 cursor-pointer"
            >
                <!-- Background visual decor -->
                <div class="absolute -top-12 -left-12 w-48 h-48 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white dark:bg-neutral-800 border border-neutral-200/60 dark:border-neutral-700/60 text-[11px] font-bold text-neutral-600 dark:text-neutral-300">
                    🤖 INTEGRASI ASISTEN AI MANDIRI
                </div>
                
                <h3 class="text-2xl md:text-3xl font-bold max-w-2xl mx-auto leading-snug">
                    Analisis Keuangan Instan dengan Gemini 2.5 Flash
                </h3>
                
                <p class="text-sm text-neutral-500 dark:text-neutral-400 max-w-xl mx-auto">
                    Sistem ini mengintegrasikan teknologi AI Generatif secara langsung menggunakan API key pribadi yayasan. Dirancang aman tanpa adanya pelacakan eksternal untuk membantu pimpinan mengolah kesimpulan laporan bulanan.
                </p>

                <div class="pt-2">
                    <span class="inline-block px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-400 bg-neutral-100 dark:bg-neutral-800 rounded-lg">
                        Pemrosesan Data Keuangan Aman &bull; Server Side Integration
                    </span>
                </div>
            </Motion>
        </section>

        <!-- Minimalist Tahoe Footer -->
        <footer class="border-t border-neutral-200/50 dark:border-neutral-800/50 bg-white/40 dark:bg-[#121214]/40 backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-4 py-12 md:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
                
                <div class="flex items-center gap-3">
                    <div class="flex aspect-square size-7 items-center justify-center rounded-lg bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 overflow-hidden font-bold text-xs select-none">
                        Y
                    </div>
                    <span class="text-xs font-semibold tracking-wider text-neutral-500 dark:text-neutral-400 uppercase">
                        {{ appName }} &copy; {{ new Date().getFullYear() }} All Rights Reserved.
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-neutral-500 dark:text-neutral-400 font-medium">
                    <span>Yayasan Pendidikan Darul Halim Madani</span>
                    <span class="text-neutral-300 dark:text-neutral-800">&bull;</span>
                    <span class="text-amber-600 dark:text-amber-400 font-semibold">Penggunaan Pribadi (Tidak untuk Dijual)</span>
                    <span class="text-neutral-300 dark:text-neutral-800">&bull;</span>
                    <span>v1.0.0-Private</span>
                </div>

            </div>
        </footer>

    </div>
</template>

<style scoped>
/* Subtle custom styling override for smooth scroll animation behavior */
html {
    scroll-behavior: smooth;
}
</style>
