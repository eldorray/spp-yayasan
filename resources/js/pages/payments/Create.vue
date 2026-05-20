<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
            { title: 'Pembayaran', href: '/payments' },
            { title: 'Input Pembayaran', href: '/payments/create' },
        ],
    },
});

type Student = {
    id: number;
    nis: string;
    name: string;
    institution: { name: string };
    placements: { classroom: { name: string } }[];
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
    student: Student | null;
    monthlyBills: MonthlyBill[];
    activityBills: ActivityBill[];
    activeYear: { id: number; name: string } | null;
}>();

const searchQuery = ref('');
const searchResults = ref<Student[]>([]);
const showResults = ref(false);
const selectedBill = ref<{ type: string; id: number; remaining: number } | null>(null);

const form = useForm({
    student_id: props.student?.id ? String(props.student.id) : '',
    bill_type: '',
    bill_id: '',
    amount: '',
    payment_method: 'cash',
    payment_date: new Date().toISOString().split('T')[0],
    notes: '',
});

const months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

async function searchStudents() {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }
    const response = await fetch(`/api/students/search?q=${encodeURIComponent(searchQuery.value)}`);
    searchResults.value = await response.json();
    showResults.value = true;
}

function selectStudent(student: Student) {
    showResults.value = false;
    searchQuery.value = '';
    router.get('/payments/create', { student_id: student.id }, { preserveState: false });
}

function selectBill(type: string, id: number, amount: number, paidAmount: number) {
    form.bill_type = type;
    form.bill_id = String(id);
    const remaining = amount - paidAmount;
    selectedBill.value = { type, id, remaining };
    form.amount = String(remaining);
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

function submit() {
    form.student_id = String(props.student?.id ?? '');
    form.post('/payments');
}
</script>

<template>
    <Head title="Input Pembayaran" />

    <div class="mx-auto max-w-3xl space-y-6 p-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Input Pembayaran</h1>
            <p class="text-sm text-muted-foreground">
                Catat pembayaran siswa
                <span v-if="activeYear"> — Tahun Ajaran {{ activeYear.name }}</span>
            </p>
        </div>

        <!-- Student Search -->
        <div v-if="!student" class="rounded-xl bg-background p-6 shadow-sm">
            <Label class="mb-2 block">Cari Siswa</Label>
            <div class="relative">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="searchQuery"
                    placeholder="Ketik nama, NIS, atau NISN siswa..."
                    class="pl-9"
                    @input="searchStudents"
                    @focus="showResults = searchResults.length > 0"
                />
                <!-- Results dropdown -->
                <div
                    v-if="showResults && searchResults.length > 0"
                    class="absolute z-10 mt-1 w-full rounded-lg border bg-background shadow-lg"
                >
                    <button
                        v-for="s in searchResults"
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

        <!-- Selected Student & Bills -->
        <template v-if="student">
            <div class="rounded-xl bg-background p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">{{ student.name }}</h3>
                        <p class="text-sm text-muted-foreground">
                            {{ student.institution.name }} — {{ student.nis }}
                            <span v-if="student.placements?.[0]"> — {{ student.placements[0].classroom.name }}</span>
                        </p>
                    </div>
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/payments/create">Ganti Siswa</Link>
                    </Button>
                </div>
            </div>

            <!-- Unpaid Bills -->
            <div class="rounded-xl bg-background p-6 shadow-sm">
                <h3 class="mb-4 font-semibold">Tagihan Belum Lunas</h3>

                <!-- Monthly Bills -->
                <div v-if="monthlyBills.length > 0" class="mb-4">
                    <p class="mb-2 text-sm font-medium text-muted-foreground">Tagihan Bulanan</p>
                    <div class="space-y-2">
                        <button
                            v-for="bill in monthlyBills"
                            :key="'m-' + bill.id"
                            class="flex w-full items-center justify-between rounded-lg border p-3 text-left transition-colors"
                            :class="selectedBill?.type === 'monthly' && selectedBill?.id === bill.id ? 'border-primary bg-primary/5' : 'hover:bg-muted'"
                            @click="selectBill('monthly', bill.id, bill.amount, bill.paid_amount)"
                        >
                            <div>
                                <p class="font-medium">{{ months[bill.month] }}</p>
                                <p class="text-xs text-muted-foreground">{{ bill.fee_rate.name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium">{{ formatCurrency(bill.amount - bill.paid_amount) }}</p>
                                <p v-if="bill.paid_amount > 0" class="text-xs text-muted-foreground">
                                    Terbayar: {{ formatCurrency(bill.paid_amount) }}
                                </p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Activity Bills -->
                <div v-if="activityBills.length > 0">
                    <p class="mb-2 text-sm font-medium text-muted-foreground">Tagihan Kegiatan</p>
                    <div class="space-y-2">
                        <button
                            v-for="bill in activityBills"
                            :key="'a-' + bill.id"
                            class="flex w-full items-center justify-between rounded-lg border p-3 text-left transition-colors"
                            :class="selectedBill?.type === 'activity' && selectedBill?.id === bill.id ? 'border-primary bg-primary/5' : 'hover:bg-muted'"
                            @click="selectBill('activity', bill.id, bill.amount, bill.paid_amount)"
                        >
                            <div>
                                <p class="font-medium">{{ bill.activity.name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium">{{ formatCurrency(bill.amount - bill.paid_amount) }}</p>
                                <p v-if="bill.paid_amount > 0" class="text-xs text-muted-foreground">
                                    Terbayar: {{ formatCurrency(bill.paid_amount) }}
                                </p>
                            </div>
                        </button>
                    </div>
                </div>

                <p v-if="monthlyBills.length === 0 && activityBills.length === 0" class="py-8 text-center text-muted-foreground">
                    Semua tagihan sudah lunas.
                </p>
            </div>

            <!-- Payment Form -->
            <div v-if="selectedBill" class="rounded-xl bg-background p-6 shadow-sm">
                <h3 class="mb-4 font-semibold">Detail Pembayaran</h3>
                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="amount">Jumlah Bayar</Label>
                            <Input id="amount" v-model="form.amount" type="number" :max="selectedBill.remaining" />
                            <p class="text-xs text-muted-foreground">Sisa: {{ formatCurrency(selectedBill.remaining) }}</p>
                            <p v-if="form.errors.amount" class="text-sm text-destructive">{{ form.errors.amount }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label>Metode Pembayaran</Label>
                            <Select v-model="form.payment_method">
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

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="payment_date">Tanggal Pembayaran</Label>
                            <Input id="payment_date" v-model="form.payment_date" type="date" />
                        </div>
                        <div class="space-y-2">
                            <Label for="notes">Catatan</Label>
                            <Input id="notes" v-model="form.notes" placeholder="Opsional" />
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <Button type="submit" :disabled="form.processing">Simpan Pembayaran</Button>
                        <Button variant="outline" as-child>
                            <Link href="/payments">Batal</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </template>
    </div>
</template>
