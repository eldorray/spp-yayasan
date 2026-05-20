<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { CreditCard, Eye, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';
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
            { title: 'Pembayaran', href: '/payments' },
        ],
    },
});

type Payment = {
    id: number;
    transaction_number: string;
    amount: number;
    payment_method: string;
    payment_date: string;
    status: string;
    notes: string | null;
    student: { id: number; name: string; nis: string; institution: { name: string } };
    academic_year: { name: string };
    creator: { name: string } | null;
};

type StudentResult = {
    id: number;
    nis: string;
    name: string;
    institution: { name: string };
};

type MonthlyBill = {
    id: number;
    month: number;
    amount: number;
    paid_amount: number;
    fee_rate: { name: string };
};

type ActivityBill = {
    id: number;
    amount: number;
    paid_amount: number;
    activity: { name: string };
};

const props = defineProps<{
    payments: PaginatedData<Payment>;
    academicYears: { id: number; name: string }[];
    activeYearId: number;
    filters: Record<string, string | null>;
}>();

const search = ref(props.filters.search ?? '');
const months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

// Payment modal state
const showPaymentDialog = ref(false);
const studentSearch = ref('');
const studentResults = ref<StudentResult[]>([]);
const showStudentResults = ref(false);
const selectedStudent = ref<StudentResult | null>(null);
const monthlyBills = ref<MonthlyBill[]>([]);
const activityBills = ref<ActivityBill[]>([]);
const selectedBill = ref<{ type: string; id: number; remaining: number } | null>(null);
const loadingBills = ref(false);

const paymentForm = useForm({
    student_id: '',
    bill_type: '',
    bill_id: '',
    amount: '',
    payment_method: 'cash',
    payment_date: new Date().toISOString().split('T')[0],
    notes: '',
});

function openPaymentDialog() {
    selectedStudent.value = null;
    studentSearch.value = '';
    studentResults.value = [];
    monthlyBills.value = [];
    activityBills.value = [];
    selectedBill.value = null;
    paymentForm.reset();
    paymentForm.clearErrors();
    paymentForm.payment_method = 'cash';
    paymentForm.payment_date = new Date().toISOString().split('T')[0];
    showPaymentDialog.value = true;
}

async function searchStudents() {
    if (studentSearch.value.length < 2) {
        studentResults.value = [];
        return;
    }
    const response = await fetch(`/api/students/search?q=${encodeURIComponent(studentSearch.value)}`);
    studentResults.value = await response.json();
    showStudentResults.value = true;
}

const debouncedStudentSearch = useDebounceFn(searchStudents, 300);
watch(studentSearch, () => debouncedStudentSearch());

async function selectStudent(student: StudentResult) {
    selectedStudent.value = student;
    showStudentResults.value = false;
    studentSearch.value = '';
    paymentForm.student_id = String(student.id);
    selectedBill.value = null;

    // Fetch bills for this student
    loadingBills.value = true;
    try {
        const response = await fetch(`/payments/create?student_id=${student.id}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await response.json();
        monthlyBills.value = data.monthlyBills ?? [];
        activityBills.value = data.activityBills ?? [];
    } catch {
        monthlyBills.value = [];
        activityBills.value = [];
    } finally {
        loadingBills.value = false;
    }
}

function selectBill(type: string, id: number, amount: number, paidAmount: number) {
    const remaining = amount - paidAmount;
    selectedBill.value = { type, id, remaining };
    paymentForm.bill_type = type;
    paymentForm.bill_id = String(id);
    paymentForm.amount = String(remaining);
}

function submitPayment() {
    paymentForm.post('/payments', {
        onSuccess: () => {
            showPaymentDialog.value = false;
        },
    });
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

// Filters
function applyFilter(key: string, value: string | null) {
    const params: Record<string, string | undefined> = {};
    Object.entries(props.filters).forEach(([k, v]) => {
        if (v) params[k] = v;
    });
    if (!value || value === 'all') {
        delete params[key];
    } else {
        params[key] = value;
    }
    params.search = search.value || undefined;
    router.get('/payments', params, { preserveState: true, replace: true });
}

const debouncedFilter = useDebounceFn(() => applyFilter('search', search.value), 300);
watch(search, () => debouncedFilter());
</script>

<template>
    <Head title="Pembayaran" />

    <div class="space-y-6 p-4">
        <!-- Prominent Input Payment Button -->
        <button
            class="flex w-full cursor-pointer items-center justify-between rounded-xl bg-emerald-600 px-6 py-5 text-left text-white shadow-md transition-colors hover:bg-emerald-700"
            @click="openPaymentDialog"
        >
            <div>
                <h2 class="text-lg font-bold">Input Pembayaran</h2>
                <p class="text-sm text-emerald-100">Catat pembayaran siswa baru</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/20">
                <CreditCard class="h-6 w-6" />
            </div>
        </button>

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Riwayat Pembayaran</h1>
                <p class="text-sm text-muted-foreground">Daftar transaksi pembayaran yang sudah dicatat</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input v-model="search" placeholder="Cari no. transaksi atau nama siswa..." class="max-w-sm pl-9" />
            </div>
            <Select :model-value="filters.status || 'all'" @update:model-value="(v) => applyFilter('status', v as string)">
                <SelectTrigger class="w-[150px]">
                    <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Semua</SelectItem>
                    <SelectItem value="valid">Valid</SelectItem>
                    <SelectItem value="cancelled">Dibatalkan</SelectItem>
                </SelectContent>
            </Select>
            <Select :model-value="filters.payment_method || 'all'" @update:model-value="(v) => applyFilter('payment_method', v as string)">
                <SelectTrigger class="w-[150px]">
                    <SelectValue placeholder="Metode" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Semua</SelectItem>
                    <SelectItem value="cash">Tunai</SelectItem>
                    <SelectItem value="transfer">Transfer</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Table -->
        <div class="rounded-xl bg-background p-6 shadow-sm">
            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>No. Transaksi</TableHead>
                            <TableHead>Siswa</TableHead>
                            <TableHead>Jumlah</TableHead>
                            <TableHead>Metode</TableHead>
                            <TableHead>Tanggal</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="w-[80px]">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="payment in payments.data" :key="payment.id">
                            <TableCell class="font-mono text-sm">{{ payment.transaction_number }}</TableCell>
                            <TableCell>
                                <div>
                                    <p class="font-medium">{{ payment.student.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ payment.student.institution.name }} - {{ payment.student.nis }}</p>
                                </div>
                            </TableCell>
                            <TableCell class="font-medium">{{ formatCurrency(payment.amount) }}</TableCell>
                            <TableCell>
                                <Badge variant="outline">{{ payment.payment_method === 'cash' ? 'Tunai' : 'Transfer' }}</Badge>
                            </TableCell>
                            <TableCell>{{ new Date(payment.payment_date).toLocaleDateString('id-ID') }}</TableCell>
                            <TableCell>
                                <Badge :variant="payment.status === 'valid' ? 'default' : 'destructive'">
                                    {{ payment.status === 'valid' ? 'Valid' : 'Batal' }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                    <Link :href="`/payments/${payment.id}`">
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="payments.data.length === 0">
                            <TableCell colspan="7" class="py-12 text-center text-muted-foreground">
                                Tidak ada transaksi ditemukan.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="payments.last_page > 1" class="mt-4 flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    Menampilkan {{ payments.from }} - {{ payments.to }} dari {{ payments.total }}
                </p>
                <div class="flex gap-1">
                    <Button v-for="link in payments.links" :key="link.label" variant="outline" size="sm" class="h-8" :disabled="!link.url || link.active" as-child>
                        <Link v-if="link.url" :href="link.url" preserve-state v-html="link.label" />
                        <span v-else v-html="link.label" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Payment Input Dialog -->
        <Dialog v-model:open="showPaymentDialog">
            <DialogContent class="max-w-xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Input Pembayaran</DialogTitle>
                    <DialogDescription>Catat pembayaran siswa</DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-4">
                    <!-- Student Search -->
                    <div v-if="!selectedStudent" class="space-y-2">
                        <Label>Cari Siswa</Label>
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="studentSearch"
                                placeholder="Ketik nama, NIS, atau NISN..."
                                class="pl-9"
                                @focus="showStudentResults = studentResults.length > 0"
                            />
                            <div
                                v-if="showStudentResults && studentResults.length > 0"
                                class="absolute z-10 mt-1 w-full rounded-lg border bg-background shadow-lg"
                            >
                                <button
                                    v-for="s in studentResults"
                                    :key="s.id"
                                    class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-muted"
                                    @click="selectStudent(s)"
                                >
                                    <div>
                                        <p class="font-medium">{{ s.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ s.institution.name }} — {{ s.nis }}</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Student -->
                    <div v-if="selectedStudent" class="flex items-center justify-between rounded-lg border p-3">
                        <div>
                            <p class="font-medium">{{ selectedStudent.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ selectedStudent.institution.name }} — {{ selectedStudent.nis }}</p>
                        </div>
                        <Button variant="ghost" size="sm" @click="selectedStudent = null; monthlyBills = []; activityBills = []; selectedBill = null;">
                            Ganti
                        </Button>
                    </div>

                    <!-- Loading Bills -->
                    <div v-if="loadingBills" class="py-4 text-center text-sm text-muted-foreground">
                        Memuat tagihan...
                    </div>

                    <!-- Bills Selection -->
                    <div v-if="selectedStudent && !loadingBills">
                        <!-- Monthly Bills -->
                        <div v-if="monthlyBills.length > 0" class="mb-3">
                            <p class="mb-2 text-sm font-medium text-muted-foreground">Tagihan Bulanan</p>
                            <div class="space-y-1.5 max-h-[150px] overflow-y-auto">
                                <button
                                    v-for="bill in monthlyBills"
                                    :key="'m-' + bill.id"
                                    class="flex w-full items-center justify-between rounded-lg border p-2.5 text-left text-sm transition-colors"
                                    :class="selectedBill?.type === 'monthly' && selectedBill?.id === bill.id ? 'border-primary bg-primary/5' : 'hover:bg-muted'"
                                    @click="selectBill('monthly', bill.id, bill.amount, bill.paid_amount)"
                                >
                                    <div>
                                        <p class="font-medium">{{ months[bill.month] }}</p>
                                        <p class="text-xs text-muted-foreground">{{ bill.fee_rate.name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium">{{ formatCurrency(bill.amount - bill.paid_amount) }}</p>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Activity Bills -->
                        <div v-if="activityBills.length > 0">
                            <p class="mb-2 text-sm font-medium text-muted-foreground">Tagihan Kegiatan</p>
                            <div class="space-y-1.5 max-h-[150px] overflow-y-auto">
                                <button
                                    v-for="bill in activityBills"
                                    :key="'a-' + bill.id"
                                    class="flex w-full items-center justify-between rounded-lg border p-2.5 text-left text-sm transition-colors"
                                    :class="selectedBill?.type === 'activity' && selectedBill?.id === bill.id ? 'border-primary bg-primary/5' : 'hover:bg-muted'"
                                    @click="selectBill('activity', bill.id, bill.amount, bill.paid_amount)"
                                >
                                    <div>
                                        <p class="font-medium">{{ bill.activity.name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium">{{ formatCurrency(bill.amount - bill.paid_amount) }}</p>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <p v-if="monthlyBills.length === 0 && activityBills.length === 0" class="py-4 text-center text-sm text-muted-foreground">
                            Semua tagihan sudah lunas.
                        </p>
                    </div>

                    <!-- Payment Form -->
                    <div v-if="selectedBill" class="space-y-3 border-t pt-4">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label for="payAmount">Jumlah Bayar</Label>
                                <Input id="payAmount" v-model="paymentForm.amount" type="number" :max="selectedBill.remaining" />
                                <p class="text-xs text-muted-foreground">Sisa: {{ formatCurrency(selectedBill.remaining) }}</p>
                                <p v-if="paymentForm.errors.amount" class="text-xs text-destructive">{{ paymentForm.errors.amount }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <Label>Metode</Label>
                                <Select v-model="paymentForm.payment_method">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="cash">Tunai</SelectItem>
                                        <SelectItem value="transfer">Transfer</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label for="payDate">Tanggal</Label>
                                <Input id="payDate" v-model="paymentForm.payment_date" type="date" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="payNotes">Catatan</Label>
                                <Input id="payNotes" v-model="paymentForm.notes" placeholder="Opsional" />
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="showPaymentDialog = false">Batal</Button>
                    <Button :disabled="!selectedBill || paymentForm.processing" @click="submitPayment">
                        Simpan Pembayaran
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
