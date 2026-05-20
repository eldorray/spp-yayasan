<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Pembayaran', href: '/payments' },
            { title: 'Detail', href: '#' },
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
    cancel_reason: string | null;
    student: { id: number; name: string; nis: string; institution: { name: string } };
    academic_year: { name: string };
    creator: { name: string } | null;
    created_at: string;
};

const props = defineProps<{ payment: Payment }>();

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

function printReceipt() {
    window.print();
}
</script>

<template>
    <Head :title="`Kuitansi - ${payment.transaction_number}`" />

    <div class="mx-auto max-w-2xl space-y-6 p-4">
        <div class="flex items-center justify-between">
            <Button variant="ghost" size="sm" as-child>
                <Link href="/payments">
                    <ArrowLeft class="mr-1 h-4 w-4" /> Kembali
                </Link>
            </Button>
            <Button size="sm" @click="printReceipt">
                <Printer class="mr-1 h-4 w-4" /> Cetak Kuitansi
            </Button>
        </div>

        <!-- Receipt -->
        <div class="rounded-xl border bg-background p-8 shadow-sm print:shadow-none print:border-none">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold">KUITANSI PEMBAYARAN</h2>
                <p class="text-sm text-muted-foreground">{{ payment.student.institution.name }}</p>
            </div>

            <div class="mb-6 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-muted-foreground">No. Transaksi</p>
                    <p class="font-mono font-medium">{{ payment.transaction_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-muted-foreground">Tanggal</p>
                    <p class="font-medium">{{ new Date(payment.payment_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                </div>
            </div>

            <div class="mb-6 space-y-3 rounded-lg bg-muted/50 p-4">
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Nama Siswa</span>
                    <span class="font-medium">{{ payment.student.name }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">NIS</span>
                    <span>{{ payment.student.nis }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Instansi</span>
                    <span>{{ payment.student.institution.name }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Tahun Ajaran</span>
                    <span>{{ payment.academic_year.name }}</span>
                </div>
            </div>

            <div class="mb-6 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Metode Pembayaran</span>
                    <span>{{ payment.payment_method === 'cash' ? 'Tunai' : 'Transfer' }}</span>
                </div>
                <div class="flex justify-between border-t pt-3">
                    <span class="text-lg font-semibold">Jumlah</span>
                    <span class="text-lg font-bold">{{ formatCurrency(payment.amount) }}</span>
                </div>
            </div>

            <div class="flex justify-between text-sm">
                <div>
                    <Badge :variant="payment.status === 'valid' ? 'default' : 'destructive'">
                        {{ payment.status === 'valid' ? 'Valid' : 'Dibatalkan' }}
                    </Badge>
                </div>
                <div class="text-right text-muted-foreground">
                    <p>Petugas: {{ payment.creator?.name ?? '-' }}</p>
                </div>
            </div>

            <div v-if="payment.notes" class="mt-4 text-sm">
                <p class="text-muted-foreground">Catatan: {{ payment.notes }}</p>
            </div>

            <div class="mt-6 border-t pt-4 text-center text-xs text-muted-foreground italic">
                Harap simpan kuitansi ini sebagai bukti pembayaran yang sah.
            </div>
        </div>
    </div>
</template>
