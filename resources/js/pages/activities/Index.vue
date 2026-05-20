<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { CalendarDays, Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';
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

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Kegiatan', href: '/activities' },
        ],
    },
});

type Activity = {
    id: number;
    name: string;
    amount: number;
    activity_date: string | null;
    description: string | null;
    institution: { id: number; name: string };
    academic_year: { id: number; name: string };
    bills_count: number;
    bills_sum_amount: number | null;
    bills_sum_paid_amount: number | null;
};

const props = defineProps<{
    activities: Activity[];
    institutions: { id: number; name: string }[];
    academicYears: { id: number; name: string }[];
    classrooms: { id: number; name: string; institution_id: number }[];
    activeYearId: number;
    filters: Record<string, string | null>;
}>();

const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const editingActivity = ref<Activity | null>(null);
const deletingActivity = ref<Activity | null>(null);

const form = useForm({
    academic_year_id: props.activeYearId ? String(props.activeYearId) : '',
    institution_id: '',
    name: '',
    amount: '',
    activity_date: '',
    description: '',
    target: 'all',
    classroom_ids: [] as string[],
});

const editForm = useForm({
    name: '',
    amount: '',
    activity_date: '',
    description: '',
});

const filteredClassrooms = computed(() => {
    if (!form.institution_id) return props.classrooms;
    return props.classrooms.filter((c) => c.institution_id === Number(form.institution_id));
});

function openCreate() {
    form.reset();
    form.clearErrors();
    form.academic_year_id = props.activeYearId ? String(props.activeYearId) : '';
    showCreateDialog.value = true;
}

function submitCreate() {
    form.post('/activities', {
        onSuccess: () => {
            showCreateDialog.value = false;
        },
    });
}

function openEdit(activity: Activity) {
    editingActivity.value = activity;
    editForm.clearErrors();
    editForm.name = activity.name;
    editForm.amount = String(activity.amount);
    editForm.activity_date = activity.activity_date ?? '';
    editForm.description = activity.description ?? '';
    showEditDialog.value = true;
}

function submitEdit() {
    if (editingActivity.value) {
        editForm.patch(`/activities/${editingActivity.value.id}`, {
            onSuccess: () => {
                showEditDialog.value = false;
            },
        });
    }
}

function openDelete(activity: Activity) {
    deletingActivity.value = activity;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (deletingActivity.value) {
        router.delete(`/activities/${deletingActivity.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                deletingActivity.value = null;
            },
        });
    }
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
}

function filterByInstitution(value: string | null) {
    router.get(
        '/activities',
        { institution_id: !value || value === 'all' ? undefined : value },
        { preserveState: true, replace: true },
    );
}
</script>

<template>
    <Head title="Kegiatan" />

    <div class="space-y-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Kegiatan</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola kegiatan dan tagihan kegiatan
                </p>
            </div>
            <Button @click="openCreate">
                <Plus class="mr-1 h-4 w-4" /> Tambah Kegiatan
            </Button>
        </div>

        <!-- Filters -->
        <div class="flex gap-3">
            <Select
                :model-value="filters.institution_id || 'all'"
                @update:model-value="filterByInstitution"
            >
                <SelectTrigger class="w-[180px]">
                    <SelectValue placeholder="Semua Instansi" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Semua Instansi</SelectItem>
                    <SelectItem
                        v-for="inst in institutions"
                        :key="inst.id"
                        :value="String(inst.id)"
                    >
                        {{ inst.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Table -->
        <div class="rounded-xl bg-background p-6 shadow-sm">
            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nama Kegiatan</TableHead>
                            <TableHead>Instansi</TableHead>
                            <TableHead>Biaya/Siswa</TableHead>
                            <TableHead>Peserta</TableHead>
                            <TableHead>Total Tagihan</TableHead>
                            <TableHead>Terbayar</TableHead>
                            <TableHead>Tanggal</TableHead>
                            <TableHead class="w-[100px]">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="activity in activities"
                            :key="activity.id"
                        >
                            <TableCell class="font-medium">
                                <div class="flex items-center gap-2">
                                    <CalendarDays
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    {{ activity.name }}
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">{{
                                    activity.institution.name
                                }}</Badge>
                            </TableCell>
                            <TableCell>{{
                                formatCurrency(activity.amount)
                            }}</TableCell>
                            <TableCell
                                >{{ activity.bills_count }} siswa</TableCell
                            >
                            <TableCell>{{
                                formatCurrency(activity.bills_sum_amount ?? 0)
                            }}</TableCell>
                            <TableCell>{{
                                formatCurrency(
                                    activity.bills_sum_paid_amount ?? 0,
                                )
                            }}</TableCell>
                            <TableCell>
                                {{
                                    activity.activity_date
                                        ? new Date(
                                              activity.activity_date,
                                          ).toLocaleDateString('id-ID')
                                        : '—'
                                }}
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        @click="openEdit(activity)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Link
                                        :href="`/activities/${activity.id}`"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-sm font-medium transition-colors hover:bg-muted"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        @click="openDelete(activity)"
                                    >
                                        <Trash2
                                            class="h-4 w-4 text-destructive"
                                        />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="activities.length === 0">
                            <TableCell
                                colspan="8"
                                class="py-12 text-center text-muted-foreground"
                            >
                                Belum ada kegiatan.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Create Dialog -->
        <Dialog v-model:open="showCreateDialog">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Tambah Kegiatan</DialogTitle>
                    <DialogDescription>
                        Buat kegiatan baru dan generate tagihan untuk siswa
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitCreate">
                    <div class="space-y-4 py-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label>Tahun Ajaran</Label>
                                <Select v-model="form.academic_year_id">
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder="Pilih Tahun Ajaran"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="ay in academicYears"
                                            :key="ay.id"
                                            :value="String(ay.id)"
                                        >
                                            {{ ay.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label>Instansi</Label>
                                <Select v-model="form.institution_id">
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder="Pilih Instansi"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="inst in institutions"
                                            :key="inst.id"
                                            :value="String(inst.id)"
                                        >
                                            {{ inst.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p
                                    v-if="form.errors.institution_id"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.institution_id }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="activityName">Nama Kegiatan</Label>
                            <Input
                                id="activityName"
                                v-model="form.name"
                                placeholder="Contoh: Study Tour Bandung"
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="activityAmount"
                                    >Biaya per Siswa (Rp)</Label
                                >
                                <Input
                                    id="activityAmount"
                                    v-model="form.amount"
                                    type="number"
                                    placeholder="500000"
                                />
                                <p
                                    v-if="form.errors.amount"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.amount }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="activityDate"
                                    >Tanggal Kegiatan</Label
                                >
                                <Input
                                    id="activityDate"
                                    v-model="form.activity_date"
                                    type="date"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="activityDesc">Deskripsi</Label>
                            <Input
                                id="activityDesc"
                                v-model="form.description"
                                placeholder="Opsional"
                            />
                        </div>

                        <div v-if="form.institution_id" class="space-y-2">
                            <Label>Target Peserta</Label>
                            <Select v-model="form.target">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all"
                                        >Semua Siswa</SelectItem
                                    >
                                    <SelectItem value="classroom"
                                        >Per Kelas</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <p class="text-xs text-muted-foreground">
                                Tagihan akan otomatis dibuat untuk siswa yang
                                dipilih.
                            </p>
                        </div>

                        <div v-if="form.target === 'classroom' && form.institution_id" class="space-y-2">
                            <Label>Pilih Kelas</Label>
                            <div class="grid grid-cols-3 gap-2 max-h-[150px] overflow-y-auto">
                                <button
                                    v-for="cls in filteredClassrooms"
                                    :key="cls.id"
                                    type="button"
                                    class="rounded-md border px-2 py-1.5 text-xs transition-colors"
                                    :class="form.classroom_ids.includes(String(cls.id)) ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-muted'"
                                    @click="form.classroom_ids.includes(String(cls.id)) ? form.classroom_ids = form.classroom_ids.filter(i => i !== String(cls.id)) : form.classroom_ids = [...form.classroom_ids, String(cls.id)]"
                                >
                                    {{ cls.name }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="showCreateDialog = false"
                        >
                            Batal
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            Simpan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit Dialog -->
        <Dialog v-model:open="showEditDialog">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Edit Kegiatan</DialogTitle>
                    <DialogDescription>
                        Perbarui informasi kegiatan
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitEdit">
                    <div class="space-y-4 py-4">
                        <!-- Read-only info -->
                        <div class="rounded-lg bg-muted/50 p-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Instansi</span>
                                <span class="font-medium">{{ editingActivity?.institution.name }}</span>
                            </div>
                            <div class="mt-1 flex justify-between">
                                <span class="text-muted-foreground">Peserta</span>
                                <span class="font-medium">{{ editingActivity?.bills_count }} siswa</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="editName">Nama Kegiatan</Label>
                            <Input id="editName" v-model="editForm.name" />
                            <p v-if="editForm.errors.name" class="text-sm text-destructive">{{ editForm.errors.name }}</p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="editAmount">Biaya per Siswa (Rp)</Label>
                                <Input id="editAmount" v-model="editForm.amount" type="number" />
                                <p v-if="editForm.errors.amount" class="text-sm text-destructive">{{ editForm.errors.amount }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="editDate">Tanggal</Label>
                                <Input id="editDate" v-model="editForm.activity_date" type="date" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="editDesc">Deskripsi</Label>
                            <Input id="editDesc" v-model="editForm.description" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showEditDialog = false">Batal</Button>
                        <Button type="submit" :disabled="editForm.processing">Simpan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Hapus Kegiatan</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus kegiatan
                        <strong>{{ deletingActivity?.name }}</strong
                        >? Semua tagihan terkait juga akan dihapus.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        variant="outline"
                        @click="showDeleteDialog = false"
                    >
                        Batal
                    </Button>
                    <Button variant="destructive" @click="confirmDelete">
                        Hapus
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
