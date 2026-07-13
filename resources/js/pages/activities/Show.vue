<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ArrowLeft, Search } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
            { title: 'Kegiatan', href: '/activities' },
            { title: 'Detail', href: '#' },
        ],
    },
});

type Activity = {
    id: number;
    name: string;
    amount: number;
    activity_date: string | null;
    description: string | null;
    institution: { name: string };
    academic_year: { name: string };
};

type Bill = {
    id: number;
    amount: number;
    paid_amount: number;
    status: string;
    student: {
        name: string;
        nis: string;
        placements: { classroom: { name: string } }[];
    };
};

const props = defineProps<{
    activity: Activity;
    bills: Bill[];
}>();

const search = ref('');

const filteredBills = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.bills;
    return props.bills.filter((bill) =>
        bill.student.name.toLowerCase().includes(q) ||
        bill.student.nis.toLowerCase().includes(q) ||
        (bill.student.placements?.[0]?.classroom?.name ?? '').toLowerCase().includes(q)
    );
});

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
</script>

<template>
    <Head :title="activity.name" />

    <div class="space-y-6 p-4">
        <div class="flex items-center gap-4">
            <Link href="/activities" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
                <ArrowLeft class="h-4 w-4" /> Kembali
            </Link>
        </div>

        <!-- Activity Info -->
        <div class="rounded-xl bg-background p-6 shadow-sm">
            <h1 class="text-2xl font-bold">{{ activity.name }}</h1>
            <div class="mt-2 flex flex-wrap gap-3 text-sm text-muted-foreground">
                <Badge variant="outline">{{ activity.institution.name }}</Badge>
                <span>{{ activity.academic_year.name }}</span>
                <span>{{ formatCurrency(activity.amount) }} / siswa</span>
                <span v-if="activity.activity_date">{{ new Date(activity.activity_date).toLocaleDateString('id-ID') }}</span>
            </div>
            <p v-if="activity.description" class="mt-3 text-sm text-muted-foreground">{{ activity.description }}</p>
        </div>

        <!-- Bills Table -->
        <div class="rounded-xl bg-background p-6 shadow-sm">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h3 class="font-semibold">Daftar Tagihan ({{ filteredBills.length }} siswa)</h3>
                <div class="relative w-full max-w-xs">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" type="search" placeholder="Cari nama, NIS, atau kelas..." class="pl-9" />
                </div>
            </div>
            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Siswa</TableHead>
                            <TableHead>Kelas</TableHead>
                            <TableHead>Tagihan</TableHead>
                            <TableHead>Terbayar</TableHead>
                            <TableHead>Sisa</TableHead>
                            <TableHead>Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="bill in filteredBills" :key="bill.id">
                            <TableCell>
                                <div>
                                    <p class="font-medium">{{ bill.student.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ bill.student.nis }}</p>
                                </div>
                            </TableCell>
                            <TableCell>{{ bill.student.placements?.[0]?.classroom?.name ?? '—' }}</TableCell>
                            <TableCell>{{ formatCurrency(bill.amount) }}</TableCell>
                            <TableCell>{{ formatCurrency(bill.paid_amount) }}</TableCell>
                            <TableCell>{{ formatCurrency(bill.amount - bill.paid_amount) }}</TableCell>
                            <TableCell>
                                <Badge :variant="statusVariant(bill.status)">{{ statusLabel(bill.status) }}</Badge>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="filteredBills.length === 0">
                            <TableCell colspan="6" class="py-12 text-center text-muted-foreground">
                                {{ bills.length === 0 ? 'Belum ada tagihan.' : 'Tidak ada hasil.' }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </div>
</template>
