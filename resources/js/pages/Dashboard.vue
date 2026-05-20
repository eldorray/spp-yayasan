<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Calendar,
    CreditCard,
    DollarSign,
    LayoutGrid,
    TrendingUp,
    UserX,
    Users,
} from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: '/dashboard' }],
    },
});

type Stats = {
    totalIncomeThisMonth: number;
    miIncome: number;
    smpIncome: number;
    unpaidCount: number;
    todayTransactions: number;
    totalStudents: number;
    activeYear: string;
};

const props = defineProps<{
    stats: Stats | null;
    noActiveYear: boolean;
}>();

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
}
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-8 p-6 max-w-7xl mx-auto">
        <!-- No Active Year Warning -->
        <div
            v-if="noActiveYear"
            class="rounded-2xl border border-amber-200/50 bg-amber-50/40 p-8 text-center backdrop-blur-xl dark:border-amber-950/50 dark:bg-amber-950/20 shadow-md max-w-lg mx-auto"
        >
            <div class="mx-auto h-14 w-14 rounded-full bg-amber-100 dark:bg-amber-900/60 flex items-center justify-center border border-amber-200 dark:border-amber-800">
                <Calendar class="h-6 w-6 text-amber-600 dark:text-amber-400" />
            </div>
            <h3 class="mt-5 text-lg font-bold text-neutral-800 dark:text-neutral-200">
                Belum Ada Tahun Ajaran Aktif
            </h3>
            <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">
                Silakan buat dan aktifkan tahun ajaran terlebih dahulu di menu
                Tahun Ajaran agar data finance terekam dengan benar.
            </p>
        </div>

        <!-- Stats Cards -->
        <template v-if="stats">
            <!-- Premium Header -->
            <div class="flex items-center justify-between border-b border-neutral-200/40 dark:border-neutral-800/40 pb-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-neutral-900 dark:bg-white flex items-center justify-center shadow-[0_4px_12px_rgba(0,0,0,0.1)]">
                        <LayoutGrid class="h-5 w-5 text-white dark:text-neutral-900" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">Finance Overview</h1>
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 flex items-center gap-1.5 mt-0.5">
                            <span class="inline-block h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Tahun Ajaran Aktif: <span class="bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded-md text-neutral-700 dark:text-neutral-300 font-mono text-[10px]">{{ stats.activeYear }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Grid of Cards -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Total Income This Month -->
                <div
                    class="group relative rounded-2xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:scale-[1.02] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] dark:hover:shadow-[0_12px_40px_rgb(0,0,0,0.2)] hover:border-neutral-300 dark:hover:border-zinc-700 transition-all duration-300 ease-out"
                >
                    <div class="flex items-start justify-between">
                        <div class="space-y-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                                Pemasukan Bulan Ini
                            </p>
                            <p class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white font-sans">
                                {{ formatCurrency(stats.totalIncomeThisMonth) }}
                            </p>
                            <span class="inline-flex items-center text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/10 mt-1">
                                Transaksi Bulan Ini
                            </span>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-b from-emerald-500/10 to-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shadow-sm"
                        >
                            <DollarSign class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <!-- MI Income -->
                <div
                    class="group relative rounded-2xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:scale-[1.02] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] dark:hover:shadow-[0_12px_40px_rgb(0,0,0,0.2)] hover:border-neutral-300 dark:hover:border-zinc-700 transition-all duration-300 ease-out"
                >
                    <div class="flex items-start justify-between">
                        <div class="space-y-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                                Total Pemasukan MI
                            </p>
                            <p class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white font-sans">
                                {{ formatCurrency(stats.miIncome) }}
                            </p>
                            <span class="inline-flex items-center text-[10px] font-semibold text-blue-600 dark:text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full border border-blue-500/10 mt-1">
                                Madrasah Ibtidaiyah
                            </span>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-b from-blue-500/10 to-blue-500/20 text-blue-600 dark:text-blue-400 border border-blue-500/20 shadow-sm"
                        >
                            <TrendingUp class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <!-- SMP Income -->
                <div
                    class="group relative rounded-2xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:scale-[1.02] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] dark:hover:shadow-[0_12px_40px_rgb(0,0,0,0.2)] hover:border-neutral-300 dark:hover:border-zinc-700 transition-all duration-300 ease-out"
                >
                    <div class="flex items-start justify-between">
                        <div class="space-y-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                                Total Pemasukan SMP
                            </p>
                            <p class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white font-sans">
                                {{ formatCurrency(stats.smpIncome) }}
                            </p>
                            <span class="inline-flex items-center text-[10px] font-semibold text-purple-600 dark:text-purple-400 bg-purple-500/10 px-2 py-0.5 rounded-full border border-purple-500/10 mt-1">
                                Sekolah Menengah Pertama
                            </span>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-b from-purple-500/10 to-purple-500/20 text-purple-600 dark:text-purple-400 border border-purple-500/20 shadow-sm"
                        >
                            <TrendingUp class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <!-- Unpaid Students -->
                <div
                    class="group relative rounded-2xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:scale-[1.02] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] dark:hover:shadow-[0_12px_40px_rgb(0,0,0,0.2)] hover:border-neutral-300 dark:hover:border-zinc-700 transition-all duration-300 ease-out"
                >
                    <div class="flex items-start justify-between">
                        <div class="space-y-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                                Siswa Belum Lunas
                            </p>
                            <p class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white font-sans">
                                {{ stats.unpaidCount }}
                            </p>
                            <span class="inline-flex items-center text-[10px] font-semibold text-red-600 dark:text-red-400 bg-red-500/10 px-2 py-0.5 rounded-full border border-red-500/10 mt-1">
                                Perlu Tindak Lanjut
                            </span>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-b from-red-500/10 to-red-500/20 text-red-600 dark:text-red-400 border border-red-500/20 shadow-sm"
                        >
                            <UserX class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <!-- Today Transactions -->
                <div
                    class="group relative rounded-2xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:scale-[1.02] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] dark:hover:shadow-[0_12px_40px_rgb(0,0,0,0.2)] hover:border-neutral-300 dark:hover:border-zinc-700 transition-all duration-300 ease-out"
                >
                    <div class="flex items-start justify-between">
                        <div class="space-y-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                                Transaksi Hari Ini
                            </p>
                            <p class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white font-sans">
                                {{ stats.todayTransactions }}
                            </p>
                            <span class="inline-flex items-center text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/10 mt-1">
                                Realtime Tracker
                            </span>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-b from-amber-500/10 to-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/20 shadow-sm"
                        >
                            <CreditCard class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <!-- Total Students -->
                <div
                    class="group relative rounded-2xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:scale-[1.02] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] dark:hover:shadow-[0_12px_40px_rgb(0,0,0,0.2)] hover:border-neutral-300 dark:hover:border-zinc-700 transition-all duration-300 ease-out"
                >
                    <div class="flex items-start justify-between">
                        <div class="space-y-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                                Total Siswa Aktif
                            </p>
                            <p class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white font-sans">
                                {{ stats.totalStudents }}
                            </p>
                            <span class="inline-flex items-center text-[10px] font-semibold text-cyan-600 dark:text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded-full border border-cyan-500/10 mt-1">
                                Terdaftar & Aktif
                            </span>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-b from-cyan-500/10 to-cyan-500/20 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 shadow-sm"
                        >
                            <Users class="h-6 w-6" />
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

