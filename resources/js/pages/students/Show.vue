<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ArrowLeft, Printer, Wallet } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
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

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Siswa', href: '/students' },
            { title: 'Detail', href: '#' },
        ],
    },
});

type Bill = {
    id: number;
    amount: number;
    paid_amount: number;
    status: string;
    payments?: { id: number }[];
};

type MonthlyBill = Bill & {
    month: number;
    academic_year: { name: string } | null;
};

type ActivityBill = Bill & {
    activity: { name: string; activity_date: string | null } | null;
};

const props = defineProps<{
    student: {
        id: number;
        name: string;
        nis: string;
        nisn: string | null;
        institution: { name: string };
        placements: { classroom: { name: string } | null; academic_year: { name: string } | null }[];
    };
    monthlyBills: MonthlyBill[];
    activityBills: ActivityBill[];
}>();

const MONTHS = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

function statusLabel(status: string): string {
    if (status === 'paid') return 'Lunas';
    if (status === 'partial') return 'Sebagian';
    return 'Belum Bayar';
}

function statusVariant(status: string) {
    if (status === 'paid') return 'default' as const;
    if (status === 'partial') return 'secondary' as const;
    return 'destructive' as const;
}

function sisa(b: Bill): number {
    return b.amount - b.paid_amount;
}

// payments come pre-sorted latest-first from the controller
function lastPaymentId(b: Bill): number | null {
    return b.payments?.[0]?.id ?? null;
}

const currentClass = computed(() => props.student.placements?.[0]?.classroom?.name ?? '—');

const totals = computed(() => {
    const all = [...props.activityBills, ...props.monthlyBills];
    const amount = all.reduce((s, b) => s + b.amount, 0);
    const paid = all.reduce((s, b) => s + b.paid_amount, 0);
    const activitySisa = props.activityBills.reduce((s, b) => s + sisa(b), 0);
    return { amount, paid, sisa: amount - paid, activitySisa };
});

const payDialog = ref(false);
const payTarget = ref<{ label: string; remaining: number } | null>(null);

const form = useForm({
    student_id: String(props.student.id),
    bill_type: '',
    bill_id: '',
    amount: '',
    payment_method: 'cash',
    payment_date: new Date().toISOString().split('T')[0],
    notes: '',
    stay: true,
});

function openPay(type: 'monthly' | 'activity', bill: Bill, label: string) {
    const remaining = sisa(bill);
    payTarget.value = { label, remaining };
    form.clearErrors();
    form.bill_type = type;
    form.bill_id = String(bill.id);
    form.amount = String(remaining);
    payDialog.value = true;
}

function submitPay() {
    form.post('/payments', {
        preserveScroll: true,
        onSuccess: () => {
            payDialog.value = false;
        },
    });
}
</script>

<template>
    <Head :title="student.name" />

    <div class="space-y-6 p-4">
        <Link href="/students" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
            <ArrowLeft class="h-4 w-4" /> Kembali
        </Link>

        <!-- Student Info -->
        <div class="rounded-xl bg-background p-6 shadow-sm">
            <h1 class="text-2xl font-bold">{{ student.name }}</h1>
            <div class="mt-2 flex flex-wrap gap-3 text-sm text-muted-foreground">
                <Badge variant="outline">{{ student.institution.name }}</Badge>
                <span>NIS: {{ student.nis }}</span>
                <span v-if="student.nisn">NISN: {{ student.nisn }}</span>
                <span>Kelas: {{ currentClass }}</span>
            </div>
        </div>

        <!-- Summary -->
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl bg-background p-4 shadow-sm">
                <p class="text-xs text-muted-foreground">Total Tagihan</p>
                <p class="mt-1 text-xl font-bold">{{ formatCurrency(totals.amount) }}</p>
            </div>
            <div class="rounded-xl bg-background p-4 shadow-sm">
                <p class="text-xs text-muted-foreground">Terbayar</p>
                <p class="mt-1 text-xl font-bold text-green-600">{{ formatCurrency(totals.paid) }}</p>
            </div>
            <div class="rounded-xl bg-background p-4 shadow-sm">
                <p class="text-xs text-muted-foreground">Sisa</p>
                <p class="mt-1 text-xl font-bold text-destructive">{{ formatCurrency(totals.sisa) }}</p>
            </div>
        </div>

        <!-- Tagihan Kegiatan (non-bulanan) -->
        <div class="rounded-xl bg-background p-6 shadow-sm">
            <h3 class="mb-4 font-semibold">
                Tagihan Kegiatan ({{ activityBills.length }})
                <span v-if="totals.activitySisa > 0" class="ml-1 text-sm font-normal text-destructive">
                    · sisa {{ formatCurrency(totals.activitySisa) }}
                </span>
            </h3>
            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Kegiatan</TableHead>
                            <TableHead>Tanggal</TableHead>
                            <TableHead>Tagihan</TableHead>
                            <TableHead>Terbayar</TableHead>
                            <TableHead>Sisa</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="bill in activityBills" :key="bill.id">
                            <TableCell class="font-medium">{{ bill.activity?.name ?? '—' }}</TableCell>
                            <TableCell>{{ bill.activity?.activity_date ? new Date(bill.activity.activity_date).toLocaleDateString('id-ID') : '—' }}</TableCell>
                            <TableCell>{{ formatCurrency(bill.amount) }}</TableCell>
                            <TableCell>{{ formatCurrency(bill.paid_amount) }}</TableCell>
                            <TableCell>{{ formatCurrency(sisa(bill)) }}</TableCell>
                            <TableCell>
                                <Badge :variant="statusVariant(bill.status)">{{ statusLabel(bill.status) }}</Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        v-if="bill.status !== 'paid'"
                                        size="sm"
                                        variant="outline"
                                        @click="openPay('activity', bill, bill.activity?.name ?? 'Kegiatan')"
                                    >
                                        <Wallet class="mr-1 h-4 w-4" /> Bayar
                                    </Button>
                                    <Button v-if="lastPaymentId(bill)" as-child size="sm" variant="ghost">
                                        <Link :href="`/payments/${lastPaymentId(bill)}`">
                                            <Printer class="mr-1 h-4 w-4" /> Struk
                                        </Link>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="activityBills.length === 0">
                            <TableCell colspan="7" class="py-10 text-center text-muted-foreground">
                                Tidak ada tagihan kegiatan.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Tagihan SPP Bulanan -->
        <div class="rounded-xl bg-background p-6 shadow-sm">
            <h3 class="mb-4 font-semibold">Tagihan SPP Bulanan ({{ monthlyBills.length }})</h3>
            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Bulan</TableHead>
                            <TableHead>Tahun Ajaran</TableHead>
                            <TableHead>Tagihan</TableHead>
                            <TableHead>Terbayar</TableHead>
                            <TableHead>Sisa</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="bill in monthlyBills" :key="bill.id">
                            <TableCell class="font-medium">{{ MONTHS[bill.month] ?? bill.month }}</TableCell>
                            <TableCell>{{ bill.academic_year?.name ?? '—' }}</TableCell>
                            <TableCell>{{ formatCurrency(bill.amount) }}</TableCell>
                            <TableCell>{{ formatCurrency(bill.paid_amount) }}</TableCell>
                            <TableCell>{{ formatCurrency(sisa(bill)) }}</TableCell>
                            <TableCell>
                                <Badge :variant="statusVariant(bill.status)">{{ statusLabel(bill.status) }}</Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        v-if="bill.status !== 'paid'"
                                        size="sm"
                                        variant="outline"
                                        @click="openPay('monthly', bill, `SPP ${MONTHS[bill.month] ?? bill.month}`)"
                                    >
                                        <Wallet class="mr-1 h-4 w-4" /> Bayar
                                    </Button>
                                    <Button v-if="lastPaymentId(bill)" as-child size="sm" variant="ghost">
                                        <Link :href="`/payments/${lastPaymentId(bill)}`">
                                            <Printer class="mr-1 h-4 w-4" /> Struk
                                        </Link>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="monthlyBills.length === 0">
                            <TableCell colspan="7" class="py-10 text-center text-muted-foreground">
                                Tidak ada tagihan bulanan.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Pay Dialog -->
        <Dialog v-model:open="payDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Bayar — {{ payTarget?.label }}</DialogTitle>
                </DialogHeader>
                <form class="space-y-4" @submit.prevent="submitPay">
                    <div class="space-y-2">
                        <Label for="amount">Jumlah Bayar</Label>
                        <Input id="amount" v-model="form.amount" type="number" min="1" :max="payTarget?.remaining" />
                        <p class="text-xs text-muted-foreground">Sisa: {{ formatCurrency(payTarget?.remaining ?? 0) }}</p>
                        <p v-if="form.errors.amount" class="text-sm text-destructive">{{ form.errors.amount }}</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label>Metode</Label>
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
                        <div class="space-y-2">
                            <Label for="payment_date">Tanggal</Label>
                            <Input id="payment_date" v-model="form.payment_date" type="date" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="notes">Catatan</Label>
                        <Input id="notes" v-model="form.notes" placeholder="Opsional" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="payDialog = false">Batal</Button>
                        <Button type="submit" :disabled="form.processing">Simpan Pembayaran</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
