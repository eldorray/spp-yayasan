<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { BarChart3, CheckCircle, Clock, XCircle } from 'lucide-vue-next';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Laporan', href: '/reports' },
        ],
    },
});

type Summary = {
    totalBilled: number;
    totalPaid: number;
    totalUnpaid: number;
    paidCount: number;
    partialCount: number;
    unpaidCount: number;
};

const props = defineProps<{
    summary: Summary;
    monthlyIncome: Record<number, number>;
    institutions: { id: number; name: string }[];
    academicYears: { id: number; name: string }[];
    classrooms: { id: number; name: string }[];
    activeYearId: number;
    filters: Record<string, string | null>;
}>();

const months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

function filterByYear(value: string | null) {
    router.get('/reports', { academic_year_id: !value || value === 'all' ? undefined : value }, { preserveState: true, replace: true });
}

const maxIncome = Math.max(...Object.values(props.monthlyIncome), 1);
</script>

<template>
    <Head title="Laporan" />

    <div class="space-y-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Laporan</h1>
                <p class="text-sm text-muted-foreground">Ringkasan keuangan dan pembayaran</p>
            </div>
            <Select :model-value="String(activeYearId)" @update:model-value="filterByYear">
                <SelectTrigger class="w-[180px]">
                    <SelectValue placeholder="Tahun Ajaran" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="ay in academicYears" :key="ay.id" :value="String(ay.id)">{{ ay.name }}</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Summary Cards -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Total Tagihan -->
            <div class="group relative rounded-2xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:scale-[1.02] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] dark:hover:shadow-[0_12px_40px_rgb(0,0,0,0.2)] hover:border-neutral-300 dark:hover:border-zinc-700 transition-all duration-300 ease-out">
                <div class="flex items-start justify-between">
                    <div class="space-y-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Total Tagihan</p>
                        <p class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white font-sans">{{ formatCurrency(summary.totalBilled) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-b from-emerald-500/10 to-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shadow-sm">
                        <BarChart3 class="h-6 w-6" />
                    </div>
                </div>
            </div>
            <!-- Total Terbayar -->
            <div class="group relative rounded-2xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:scale-[1.02] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] dark:hover:shadow-[0_12px_40px_rgb(0,0,0,0.2)] hover:border-neutral-300 dark:hover:border-zinc-700 transition-all duration-300 ease-out">
                <div class="flex items-start justify-between">
                    <div class="space-y-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Total Terbayar</p>
                        <p class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white font-sans">{{ formatCurrency(summary.totalPaid) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-b from-blue-500/10 to-blue-500/20 text-blue-600 dark:text-blue-400 border border-blue-500/20 shadow-sm">
                        <CheckCircle class="h-6 w-6" />
                    </div>
                </div>
            </div>
            <!-- Total Tunggakan -->
            <div class="group relative rounded-2xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:scale-[1.02] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] dark:hover:shadow-[0_12px_40px_rgb(0,0,0,0.2)] hover:border-neutral-300 dark:hover:border-zinc-700 transition-all duration-300 ease-out">
                <div class="flex items-start justify-between">
                    <div class="space-y-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Total Tunggakan</p>
                        <p class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white font-sans">{{ formatCurrency(summary.totalUnpaid) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-b from-red-500/10 to-red-500/20 text-red-600 dark:text-red-400 border border-red-500/20 shadow-sm">
                        <XCircle class="h-6 w-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div class="rounded-2xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/40 dark:bg-zinc-900/40 backdrop-blur-xl p-6 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-4">Status Tagihan Bulanan</h3>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="flex items-center gap-4 rounded-xl border border-neutral-200/40 dark:border-neutral-800/40 bg-white/70 dark:bg-zinc-900/60 p-4 shadow-xs">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shadow-xs">
                        <CheckCircle class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white">{{ summary.paidCount }}</p>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Lunas</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 rounded-xl border border-neutral-200/40 dark:border-neutral-800/40 bg-white/70 dark:bg-zinc-900/60 p-4 shadow-xs">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 shadow-xs">
                        <Clock class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white">{{ summary.partialCount }}</p>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Sebagian</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 rounded-xl border border-neutral-200/40 dark:border-neutral-800/40 bg-white/70 dark:bg-zinc-900/60 p-4 shadow-xs">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20 shadow-xs">
                        <XCircle class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white">{{ summary.unpaidCount }}</p>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Belum Bayar</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Income Chart -->
        <div class="rounded-2xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/40 dark:bg-zinc-900/40 backdrop-blur-xl p-6 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-4">Pemasukan per Bulan</h3>
            <div class="flex items-end gap-3" style="height: 200px">
                <div
                    v-for="m in 12"
                    :key="m"
                    class="flex flex-1 flex-col items-center gap-2 group cursor-pointer"
                >
                    <div
                        class="w-full rounded-t-lg bg-gradient-to-b from-neutral-800 to-neutral-900 hover:from-neutral-900 hover:to-black dark:from-white/80 dark:to-white/40 dark:hover:from-white dark:hover:to-white/80 transition-all duration-300 shadow-sm relative"
                        :style="{ height: monthlyIncome[m] ? `${(monthlyIncome[m] / maxIncome) * 160}px` : '4px', minHeight: '4px' }"
                    >
                        <!-- Tooltip on hover -->
                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 scale-0 group-hover:scale-100 bg-neutral-950 text-white text-[10px] px-2 py-0.5 rounded shadow-md pointer-events-none transition-all duration-200 font-mono whitespace-nowrap z-10">
                            {{ formatCurrency(monthlyIncome[m] || 0) }}
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-neutral-400 dark:text-neutral-500 group-hover:text-neutral-800 dark:group-hover:text-neutral-200 transition-colors">{{ months[m] }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
