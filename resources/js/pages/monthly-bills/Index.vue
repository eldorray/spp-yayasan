<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Receipt, Search, Settings, Trash2 } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { PaginatedData } from '@/types/admin';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Tagihan Bulanan', href: '/monthly-bills' },
        ],
    },
});

type Bill = {
    id: number;
    month: number;
    amount: number;
    paid_amount: number;
    status: string;
    student: {
        id: number;
        name: string;
        nis: string;
        institution: { id: number; name: string };
        placements: { classroom: { name: string } }[];
    };
    fee_rate: { id: number; name: string; amount: number };
};

type FeeRate = { id: number; name: string; amount: number; institution_id: number; academic_year_id: number };
type Summary = { totalBilled: number; totalPaid: number; totalUnpaid: number; unpaidCount: number; partialCount: number; paidCount: number };

const props = defineProps<{
    bills: PaginatedData<Bill>;
    summary: Summary;
    institutions: { id: number; name: string }[];
    classrooms: { id: number; name: string }[];
    academicYears: { id: number; name: string }[];
    feeRates: FeeRate[];
    activeYearId: number;
    filters: Record<string, string | null>;
}>();

const search = ref(props.filters.search ?? '');

// Dialogs
const showGenerateDialog = ref(false);
const showFeeRateDialog = ref(false);
const showBulkDeleteDialog = ref(false);

// Bulk selection
const selectedIds = ref<number[]>([]);
const headerCheckbox = ref<HTMLInputElement | null>(null);

const deletableBills = computed(() => props.bills.data.filter((b) => b.status === 'unpaid'));

const isAllSelected = computed(() =>
    deletableBills.value.length > 0 && deletableBills.value.every((b) => selectedIds.value.includes(b.id)),
);
const isSomeSelected = computed(() => selectedIds.value.length > 0 && !isAllSelected.value);

watch([isAllSelected, isSomeSelected], () => {
    if (headerCheckbox.value) {
        headerCheckbox.value.indeterminate = isSomeSelected.value;
    }
});

function toggleAll(checked: boolean) {
    selectedIds.value = checked ? deletableBills.value.map((b) => b.id) : [];
}

function toggleOne(id: number, checked: boolean) {
    if (checked) {
        if (!selectedIds.value.includes(id)) selectedIds.value = [...selectedIds.value, id];
    } else {
        selectedIds.value = selectedIds.value.filter((i) => i !== id);
    }
}

function openBulkDelete() {
    showBulkDeleteDialog.value = true;
}

function confirmBulkDelete() {
    router.post('/monthly-bills/bulk-delete', { ids: selectedIds.value }, {
        onSuccess: () => {
            showBulkDeleteDialog.value = false;
            selectedIds.value = [];
        },
    });
}

// Generate form
const generateForm = useForm({
    fee_rate_id: '',
    months: [] as number[],
    target: 'all',
    institution_id: '',
    classroom_id: '',
});

const allMonths = [
    { value: 1, label: 'Januari' },
    { value: 2, label: 'Februari' },
    { value: 3, label: 'Maret' },
    { value: 4, label: 'April' },
    { value: 5, label: 'Mei' },
    { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' },
    { value: 8, label: 'Agustus' },
    { value: 9, label: 'September' },
    { value: 10, label: 'Oktober' },
    { value: 11, label: 'November' },
    { value: 12, label: 'Desember' },
];

function openGenerate() {
    generateForm.reset();
    generateForm.clearErrors();
    showGenerateDialog.value = true;
}

const generateFilteredClassrooms = computed(() => {
    if (!generateForm.institution_id) return props.classrooms;
    return props.classrooms.filter((c: any) =>
        c.institution_id === Number(generateForm.institution_id),
    );
});

function toggleMonth(month: number) {
    if (generateForm.months.includes(month)) {
        generateForm.months = generateForm.months.filter((m) => m !== month);
    } else {
        generateForm.months = [...generateForm.months, month];
    }
}

function selectAllMonths() {
    generateForm.months = allMonths.map((m) => m.value);
}

function submitGenerate() {
    generateForm.post('/monthly-bills/generate', {
        onSuccess: () => {
            showGenerateDialog.value = false;
        },
    });
}

// Fee Rate form
const feeRateForm = useForm({
    academic_year_id: String(props.activeYearId),
    institution_id: '',
    name: '',
    amount: '',
});

const editingFeeRate = ref<FeeRate | null>(null);
const showDeleteFeeRateDialog = ref(false);
const deletingFeeRate = ref<FeeRate | null>(null);

function openFeeRate() {
    editingFeeRate.value = null;
    feeRateForm.reset();
    feeRateForm.clearErrors();
    feeRateForm.academic_year_id = String(props.activeYearId);
    showFeeRateDialog.value = true;
}

function editFeeRate(rate: FeeRate) {
    editingFeeRate.value = rate;
    feeRateForm.clearErrors();
    feeRateForm.academic_year_id = String(rate.academic_year_id);
    feeRateForm.institution_id = String(rate.institution_id);
    feeRateForm.name = rate.name;
    feeRateForm.amount = String(rate.amount);
}

function cancelEditFeeRate() {
    editingFeeRate.value = null;
    feeRateForm.reset();
    feeRateForm.clearErrors();
    feeRateForm.academic_year_id = String(props.activeYearId);
}

function submitFeeRate() {
    if (editingFeeRate.value) {
        feeRateForm.patch(`/fee-rates/${editingFeeRate.value.id}`, {
            onSuccess: () => {
                editingFeeRate.value = null;
                feeRateForm.reset();
                feeRateForm.clearErrors();
                feeRateForm.academic_year_id = String(props.activeYearId);
            },
        });
    } else {
        feeRateForm.post('/fee-rates', {
            onSuccess: () => {
                feeRateForm.reset();
                feeRateForm.clearErrors();
                feeRateForm.academic_year_id = String(props.activeYearId);
            },
        });
    }
}

function openDeleteFeeRate(rate: FeeRate) {
    deletingFeeRate.value = rate;
    showDeleteFeeRateDialog.value = true;
}

function confirmDeleteFeeRate() {
    if (deletingFeeRate.value) {
        router.delete(`/fee-rates/${deletingFeeRate.value.id}`, {
            onSuccess: () => {
                showDeleteFeeRateDialog.value = false;
                deletingFeeRate.value = null;
            },
        });
    }
}

// Filters
const months = allMonths;

function monthName(month: number): string {
    return months.find((m) => m.value === month)?.label ?? '';
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

function statusVariant(status: string) {
    if (status === 'paid') return 'default' as const;
    if (status === 'partial') return 'secondary' as const;
    return 'destructive' as const;
}

function statusLabel(status: string): string {
    if (status === 'paid') return 'Lunas';
    if (status === 'partial') return 'Sebagian';
    return 'Belum Bayar';
}

function applyFilters(key: string, value: string | null) {
    const params: Record<string, string | undefined> = {};
    Object.entries(props.filters).forEach(([k, v]) => {
        if (v) params[k] = v;
    });
    if (!value || value === 'all') {
        delete params[key];
    } else {
        params[key] = value;
    }
    if (key !== 'search') params.search = search.value || undefined;
    router.get('/monthly-bills', params, { preserveState: true, replace: true });
}

const debouncedSearch = useDebounceFn(() => applyFilters('search', search.value), 300);
watch(search, () => debouncedSearch());
</script>

<template>
    <Head title="Tagihan Bulanan" />

    <div class="space-y-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Tagihan Bulanan</h1>
                <p class="text-sm text-muted-foreground">Kelola tagihan SPP bulanan siswa</p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="openFeeRate">
                    <Settings class="mr-1 h-4 w-4" /> Tarif
                </Button>
                <Button @click="openGenerate">
                    <Plus class="mr-1 h-4 w-4" /> Generate Tagihan
                </Button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Tagihan -->
            <div class="group relative rounded-xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl p-5 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:scale-[1.02] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] dark:hover:shadow-[0_12px_40px_rgb(0,0,0,0.2)] hover:border-neutral-300 dark:hover:border-zinc-700 transition-all duration-300 ease-out">
                <p class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Total Tagihan</p>
                <p class="mt-1.5 text-xl font-extrabold tracking-tight text-neutral-900 dark:text-white font-sans">{{ formatCurrency(summary.totalBilled) }}</p>
            </div>
            <!-- Terbayar -->
            <div class="group relative rounded-xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl p-5 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:scale-[1.02] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] dark:hover:shadow-[0_12px_40px_rgb(0,0,0,0.2)] hover:border-neutral-300 dark:hover:border-zinc-700 transition-all duration-300 ease-out">
                <p class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Terbayar</p>
                <p class="mt-1.5 text-xl font-extrabold tracking-tight text-neutral-900 dark:text-white font-sans">{{ formatCurrency(summary.totalPaid) }}</p>
            </div>
            <!-- Tunggakan -->
            <div class="group relative rounded-xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl p-5 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:scale-[1.02] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] dark:hover:shadow-[0_12px_40px_rgb(0,0,0,0.2)] hover:border-neutral-300 dark:hover:border-zinc-700 transition-all duration-300 ease-out">
                <p class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Tunggakan</p>
                <p class="mt-1.5 text-xl font-extrabold tracking-tight text-neutral-900 dark:text-white font-sans">{{ formatCurrency(summary.totalUnpaid) }}</p>
            </div>
            <!-- Lunas / Sebagian / Belum -->
            <div class="group relative rounded-xl border border-neutral-200/50 dark:border-neutral-800/50 bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl p-5 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:scale-[1.02] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] dark:hover:shadow-[0_12px_40px_rgb(0,0,0,0.2)] hover:border-neutral-300 dark:hover:border-zinc-700 transition-all duration-300 ease-out">
                <p class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Lunas / Sebagian / Belum</p>
                <p class="mt-1.5 text-xl font-extrabold tracking-tight text-neutral-900 dark:text-white font-sans">{{ summary.paidCount }} / {{ summary.partialCount }} / {{ summary.unpaidCount }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input v-model="search" placeholder="Cari siswa..." class="max-w-sm pl-9" />
            </div>
            <Select :model-value="filters.institution_id || 'all'" @update:model-value="(v) => applyFilters('institution_id', v as string)">
                <SelectTrigger class="w-[150px]">
                    <SelectValue placeholder="Instansi" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Semua</SelectItem>
                    <SelectItem v-for="inst in institutions" :key="inst.id" :value="String(inst.id)">{{ inst.name }}</SelectItem>
                </SelectContent>
            </Select>
            <Select :model-value="filters.month || 'all'" @update:model-value="(v) => applyFilters('month', v as string)">
                <SelectTrigger class="w-[150px]">
                    <SelectValue placeholder="Bulan" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Semua Bulan</SelectItem>
                    <SelectItem v-for="m in months" :key="m.value" :value="String(m.value)">{{ m.label }}</SelectItem>
                </SelectContent>
            </Select>
            <Select :model-value="filters.status || 'all'" @update:model-value="(v) => applyFilters('status', v as string)">
                <SelectTrigger class="w-[150px]">
                    <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Semua Status</SelectItem>
                    <SelectItem value="unpaid">Belum Bayar</SelectItem>
                    <SelectItem value="partial">Sebagian</SelectItem>
                    <SelectItem value="paid">Lunas</SelectItem>
                </SelectContent>
            </Select>
            <Select :model-value="filters.per_page || '25'" @update:model-value="(v) => applyFilters('per_page', v as string)">
                <SelectTrigger class="w-[130px]">
                    <SelectValue placeholder="Per halaman" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="10">10 / halaman</SelectItem>
                    <SelectItem value="25">25 / halaman</SelectItem>
                    <SelectItem value="100">100 / halaman</SelectItem>
                    <SelectItem value="all">Semua</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Bulk Action Bar -->
        <div v-if="selectedIds.length > 0" class="flex items-center gap-3 rounded-lg border border-destructive/20 bg-destructive/5 px-4 py-3">
            <span class="text-sm font-medium">{{ selectedIds.length }} tagihan dipilih</span>
            <Button variant="destructive" size="sm" @click="openBulkDelete">
                <Trash2 class="mr-1 h-4 w-4" /> Hapus Terpilih
            </Button>
            <Button variant="ghost" size="sm" @click="selectedIds = []">Batal Pilih</Button>
        </div>

        <!-- Table -->
        <div class="rounded-xl bg-background p-6 shadow-sm">
            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[40px]">
                                <input
                                    ref="headerCheckbox"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-input accent-primary"
                                    :checked="isAllSelected"
                                    :disabled="deletableBills.length === 0"
                                    @change="toggleAll(($event.target as HTMLInputElement).checked)"
                                />
                            </TableHead>
                            <TableHead>Siswa</TableHead>
                            <TableHead>Instansi</TableHead>
                            <TableHead>Kelas</TableHead>
                            <TableHead>Bulan</TableHead>
                            <TableHead>Tagihan</TableHead>
                            <TableHead>Terbayar</TableHead>
                            <TableHead>Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="bill in bills.data" :key="bill.id">
                            <TableCell>
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-input accent-primary"
                                    :checked="selectedIds.includes(bill.id)"
                                    :disabled="bill.status !== 'unpaid'"
                                    @change="toggleOne(bill.id, ($event.target as HTMLInputElement).checked)"
                                />
                            </TableCell>
                            <TableCell>
                                <div>
                                    <p class="font-medium">{{ bill.student.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ bill.student.nis }}</p>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">{{ bill.student.institution.name }}</Badge>
                            </TableCell>
                            <TableCell>{{ bill.student.placements?.[0]?.classroom?.name ?? '—' }}</TableCell>
                            <TableCell>{{ monthName(bill.month) }}</TableCell>
                            <TableCell>{{ formatCurrency(bill.amount) }}</TableCell>
                            <TableCell>{{ formatCurrency(bill.paid_amount) }}</TableCell>
                            <TableCell>
                                <Badge :variant="statusVariant(bill.status)">{{ statusLabel(bill.status) }}</Badge>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="bills.data.length === 0">
                            <TableCell colspan="8" class="py-12 text-center text-muted-foreground">
                                Tidak ada tagihan ditemukan.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="bills.last_page > 1" class="mt-4 flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    Menampilkan {{ bills.from }} - {{ bills.to }} dari {{ bills.total }}
                </p>
                <div class="flex gap-1">
                    <Button v-for="link in bills.links" :key="link.label" variant="outline" size="sm" class="h-8" :disabled="!link.url || link.active" as-child>
                        <Link v-if="link.url" :href="link.url" preserve-state v-html="link.label" />
                        <span v-else v-html="link.label" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Generate Dialog -->
        <Dialog v-model:open="showGenerateDialog">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Generate Tagihan Bulanan</DialogTitle>
                    <DialogDescription>
                        Buat tagihan bulanan untuk siswa berdasarkan tarif yang dipilih.
                        Siswa SMP domisili Kota Tangerang otomatis dikecualikan.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitGenerate">
                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label>Tarif Tagihan</Label>
                            <Select v-model="generateForm.fee_rate_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Tarif" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="rate in feeRates" :key="rate.id" :value="String(rate.id)">
                                        {{ rate.name }} — {{ formatCurrency(rate.amount) }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="generateForm.errors.fee_rate_id" class="text-sm text-destructive">{{ generateForm.errors.fee_rate_id }}</p>
                            <p v-if="feeRates.length === 0" class="text-sm text-amber-600">
                                Belum ada tarif. Tambahkan tarif terlebih dahulu.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <Label>Bulan</Label>
                                <Button type="button" variant="link" size="sm" class="h-auto p-0 text-xs" @click="selectAllMonths">
                                    Pilih Semua
                                </Button>
                            </div>
                            <div class="grid grid-cols-4 gap-2">
                                <button
                                    v-for="m in allMonths"
                                    :key="m.value"
                                    type="button"
                                    class="rounded-md border px-3 py-2 text-sm transition-colors"
                                    :class="generateForm.months.includes(m.value) ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-muted'"
                                    @click="toggleMonth(m.value)"
                                >
                                    {{ m.label.substring(0, 3) }}
                                </button>
                            </div>
                            <p v-if="generateForm.errors.months" class="text-sm text-destructive">{{ generateForm.errors.months }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label>Instansi</Label>
                            <Select v-model="generateForm.institution_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Instansi" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="inst in institutions" :key="inst.id" :value="String(inst.id)">{{ inst.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div v-if="generateForm.institution_id" class="space-y-2">
                            <Label>Target</Label>
                            <Select v-model="generateForm.target">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Siswa</SelectItem>
                                    <SelectItem value="classroom">Per Kelas</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div v-if="generateForm.target === 'classroom' && generateForm.institution_id" class="space-y-2">
                            <Label>Kelas</Label>
                            <Select v-model="generateForm.classroom_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Kelas" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="cls in generateFilteredClassrooms" :key="cls.id" :value="String(cls.id)">{{ cls.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showGenerateDialog = false">Batal</Button>
                        <Button type="submit" :disabled="generateForm.processing">Generate</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Fee Rate Dialog -->
        <Dialog v-model:open="showFeeRateDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Kelola Tarif Tagihan</DialogTitle>
                    <DialogDescription>
                        Tarif berlaku per tahun ajaran dan instansi.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitFeeRate">
                    <div class="space-y-4 py-4">
                        <!-- Existing fee rates -->
                        <div v-if="feeRates.length > 0" class="space-y-2">
                            <Label class="text-muted-foreground">Tarif yang sudah ada:</Label>
                            <div class="space-y-1">
                                <div v-for="rate in feeRates" :key="rate.id" class="flex items-center justify-between rounded border px-3 py-2 text-sm">
                                    <div>
                                        <span class="font-medium">{{ rate.name }}</span>
                                        <span class="ml-2 text-muted-foreground">{{ formatCurrency(rate.amount) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <Button type="button" variant="ghost" size="icon" class="h-7 w-7" @click="editFeeRate(rate)">
                                            <Pencil class="h-3.5 w-3.5" />
                                        </Button>
                                        <Button type="button" variant="ghost" size="icon" class="h-7 w-7" @click="openDeleteFeeRate(rate)">
                                            <Trash2 class="h-3.5 w-3.5 text-destructive" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <p class="mb-3 text-sm font-medium">
                                {{ editingFeeRate ? 'Edit Tarif' : 'Tambah Tarif Baru' }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label>Instansi</Label>
                            <Select v-model="feeRateForm.institution_id" :disabled="!!editingFeeRate">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Instansi" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="inst in institutions" :key="inst.id" :value="String(inst.id)">{{ inst.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="feeRateForm.errors.institution_id" class="text-sm text-destructive">{{ feeRateForm.errors.institution_id }}</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="feeRateName">Nama Tarif</Label>
                                <Input id="feeRateName" v-model="feeRateForm.name" placeholder="Contoh: SPP Bulanan" />
                                <p v-if="feeRateForm.errors.name" class="text-sm text-destructive">{{ feeRateForm.errors.name }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="feeRateAmount">Nominal (Rp)</Label>
                                <Input id="feeRateAmount" v-model="feeRateForm.amount" type="number" placeholder="250000" />
                                <p v-if="feeRateForm.errors.amount" class="text-sm text-destructive">{{ feeRateForm.errors.amount }}</p>
                            </div>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button v-if="editingFeeRate" type="button" variant="ghost" size="sm" @click="cancelEditFeeRate">
                            Batal Edit
                        </Button>
                        <Button type="button" variant="outline" @click="showFeeRateDialog = false">Tutup</Button>
                        <Button type="submit" :disabled="feeRateForm.processing">
                            {{ editingFeeRate ? 'Simpan Perubahan' : 'Tambah Tarif' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Fee Rate Dialog -->
        <Dialog v-model:open="showDeleteFeeRateDialog">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Hapus Tarif</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus tarif
                        <strong>{{ deletingFeeRate?.name }}</strong>?
                        Tarif yang sudah memiliki tagihan tidak dapat dihapus.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteFeeRateDialog = false">Batal</Button>
                    <Button variant="destructive" @click="confirmDeleteFeeRate">Hapus</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Bulk Delete Dialog -->
        <Dialog v-model:open="showBulkDeleteDialog">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Hapus Tagihan Terpilih</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus <strong>{{ selectedIds.length }}</strong> tagihan yang dipilih?
                        Hanya tagihan yang belum dibayar yang akan dihapus.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showBulkDeleteDialog = false">Batal</Button>
                    <Button variant="destructive" @click="confirmBulkDelete">Hapus {{ selectedIds.length }} Tagihan</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
