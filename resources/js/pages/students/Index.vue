<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Plus, RefreshCw, Search, Pencil, Trash2, Users } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
            { title: 'Siswa', href: '/students' },
        ],
    },
});

type Student = {
    id: number;
    nis: string;
    nisn: string | null;
    name: string;
    domicile: string;
    is_active: boolean;
    institution: { id: number; name: string; code: string };
    placements: { id: number; classroom: { id: number; name: string } }[];
};

type Institution = { id: number; name: string; code: string };
type Classroom = { id: number; name: string; institution?: { id: number } };

const props = defineProps<{
    students: PaginatedData<Student>;
    institutions: Institution[];
    classrooms: Classroom[];
    academicYears: { id: number; name: string }[];
    activeYearId: number;
    filters: Record<string, string | null>;
}>();

const search = ref(props.filters.search ?? '');

// Modal states
const showFormDialog = ref(false);
const showDeleteDialog = ref(false);
const showBulkDeleteDialog = ref(false);
const showSyncDialog = ref(false);
const editingStudent = ref<Student | null>(null);
const deletingStudent = ref<Student | null>(null);

// Bulk selection
const selectedIds = ref<number[]>([]);
const headerCheckbox = ref<HTMLInputElement | null>(null);

const isAllSelected = computed(() => {
    return (
        props.students.data.length > 0 &&
        props.students.data.every((s) => selectedIds.value.includes(s.id))
    );
});

const isSomeSelected = computed(() => {
    return (
        selectedIds.value.length > 0 &&
        !isAllSelected.value
    );
});

watch([isAllSelected, isSomeSelected], () => {
    if (headerCheckbox.value) {
        headerCheckbox.value.indeterminate = isSomeSelected.value;
    }
});

function toggleAll(checked: boolean) {
    if (checked) {
        selectedIds.value = props.students.data.map((s) => s.id);
    } else {
        selectedIds.value = [];
    }
}

function toggleOne(id: number, checked: boolean) {
    if (checked) {
        if (!selectedIds.value.includes(id)) {
            selectedIds.value = [...selectedIds.value, id];
        }
    } else {
        selectedIds.value = selectedIds.value.filter((i) => i !== id);
    }
}

function openBulkDelete() {
    showBulkDeleteDialog.value = true;
}

function confirmBulkDelete() {
    router.post(
        '/students/bulk-delete',
        { ids: selectedIds.value },
        {
            onSuccess: () => {
                showBulkDeleteDialog.value = false;
                selectedIds.value = [];
            },
        },
    );
}

const form = useForm({
    institution_id: '',
    nis: '',
    nisn: '',
    name: '',
    domicile: '',
    is_active: true,
    classroom_id: '',
});

const syncForm = useForm({
    source: '',
});

const filteredClassrooms = computed(() => {
    if (!form.institution_id) return props.classrooms;
    return props.classrooms.filter(
        (c) => !c.institution || String(c.institution.id) === form.institution_id,
    );
});

function openCreate() {
    editingStudent.value = null;
    form.reset();
    form.clearErrors();
    form.is_active = true;
    showFormDialog.value = true;
}

function openEdit(student: Student) {
    editingStudent.value = student;
    form.clearErrors();
    form.institution_id = String(student.institution.id);
    form.nis = student.nis;
    form.nisn = student.nisn ?? '';
    form.name = student.name;
    form.domicile = student.domicile;
    form.is_active = student.is_active;
    form.classroom_id = student.placements?.[0]?.classroom?.id
        ? String(student.placements[0].classroom.id)
        : '';
    showFormDialog.value = true;
}

function submitForm() {
    if (editingStudent.value) {
        form.patch(`/students/${editingStudent.value.id}`, {
            onSuccess: () => {
                showFormDialog.value = false;
            },
        });
    } else {
        form.post('/students', {
            onSuccess: () => {
                showFormDialog.value = false;
            },
        });
    }
}

function openDelete(student: Student) {
    deletingStudent.value = student;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (deletingStudent.value) {
        router.delete(`/students/${deletingStudent.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                deletingStudent.value = null;
            },
        });
    }
}

function openSync() {
    syncForm.reset();
    syncForm.clearErrors();
    showSyncDialog.value = true;
}

function submitSync() {
    syncForm.post('/students/sync', {
        onSuccess: () => {
            showSyncDialog.value = false;
        },
    });
}

function handleSearch() {
    router.get(
        '/students',
        {
            search: search.value || undefined,
            institution_id: props.filters.institution_id || undefined,
            classroom_id: props.filters.classroom_id || undefined,
            per_page: props.filters.per_page || undefined,
        },
        { preserveState: true, replace: true },
    );
}

const debouncedSearch = useDebounceFn(handleSearch, 300);

watch(search, () => {
    debouncedSearch();
});

function filterByInstitution(value: string | null) {
    router.get(
        '/students',
        {
            institution_id: !value || value === 'all' ? undefined : value,
            search: search.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function filterByClassroom(value: string | null) {
    router.get(
        '/students',
        {
            classroom_id: !value || value === 'all' ? undefined : value,
            institution_id: props.filters.institution_id || undefined,
            search: search.value || undefined,
            per_page: props.filters.per_page || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function changePerPage(value: string | null) {
    router.get(
        '/students',
        {
            per_page: !value || value === '20' ? undefined : value,
            academic_year_id: props.filters.academic_year_id || undefined,
            institution_id: props.filters.institution_id || undefined,
            classroom_id: props.filters.classroom_id || undefined,
            search: search.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function filterByYear(value: string | null) {
    router.get(
        '/students',
        {
            academic_year_id: !value || value === String(props.activeYearId) ? undefined : value,
            search: search.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function domicileLabel(domicile: string): string {
    return domicile === 'kota_tangerang'
        ? 'Kota Tangerang'
        : 'Luar Kota Tangerang';
}
</script>

<template>
    <Head title="Siswa" />

    <div class="space-y-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Siswa</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola data siswa MI dan SMP
                </p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="openSync">
                    <RefreshCw class="mr-1 h-4 w-4" /> Sync Data Induk
                </Button>
                <Button @click="openCreate">
                    <Plus class="mr-1 h-4 w-4" /> Tambah Siswa
                </Button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                class="rounded-xl border-l-4 border-l-violet-400 bg-background p-5 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">
                            Total Siswa
                        </p>
                        <p class="mt-1 text-2xl font-bold">
                            {{ students.total }}
                        </p>
                    </div>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-violet-100"
                    >
                        <Users class="h-5 w-5 text-violet-600" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1">
                <Search
                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    placeholder="Cari nama, NIS, atau NISN..."
                    class="max-w-sm pl-9"
                />
            </div>
            <Select
                :model-value="filters.academic_year_id || String(activeYearId)"
                @update:model-value="filterByYear"
            >
                <SelectTrigger class="w-[160px]">
                    <SelectValue placeholder="Tahun Ajaran" />
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
            <Select
                :model-value="filters.institution_id || 'all'"
                @update:model-value="filterByInstitution"
            >
                <SelectTrigger class="w-[160px]">
                    <SelectValue placeholder="Instansi" />
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
            <Select
                :model-value="filters.classroom_id || 'all'"
                @update:model-value="filterByClassroom"
            >
                <SelectTrigger class="w-[180px]">
                    <SelectValue placeholder="Kelas" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Semua Kelas</SelectItem>
                    <SelectItem
                        v-for="cls in classrooms"
                        :key="cls.id"
                        :value="String(cls.id)"
                    >
                        {{ cls.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <Select
                :model-value="filters.per_page || '20'"
                @update:model-value="changePerPage"
            >
                <SelectTrigger class="w-[130px]">
                    <SelectValue placeholder="Per halaman" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="10">10 / halaman</SelectItem>
                    <SelectItem value="20">20 / halaman</SelectItem>
                    <SelectItem value="100">100 / halaman</SelectItem>
                    <SelectItem value="all">Semua</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Bulk Action Bar -->
        <div
            v-if="selectedIds.length > 0"
            class="flex items-center gap-3 rounded-lg border border-destructive/20 bg-destructive/5 px-4 py-3"
        >
            <span class="text-sm font-medium">
                {{ selectedIds.length }} siswa dipilih
            </span>
            <Button
                variant="destructive"
                size="sm"
                @click="openBulkDelete"
            >
                <Trash2 class="mr-1 h-4 w-4" /> Hapus Terpilih
            </Button>
            <Button
                variant="ghost"
                size="sm"
                @click="selectedIds = []"
            >
                Batal Pilih
            </Button>
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
                                    @change="toggleAll(($event.target as HTMLInputElement).checked)"
                                />
                            </TableHead>
                            <TableHead>NIS</TableHead>
                            <TableHead>Nama</TableHead>
                            <TableHead>Instansi</TableHead>
                            <TableHead>Kelas</TableHead>
                            <TableHead>Domisili</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="w-[100px]">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="student in students.data"
                            :key="student.id"
                        >
                            <TableCell>
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-input accent-primary"
                                    :checked="selectedIds.includes(student.id)"
                                    @change="toggleOne(student.id, ($event.target as HTMLInputElement).checked)"
                                />
                            </TableCell>
                            <TableCell class="font-mono text-sm">{{
                                student.nis
                            }}</TableCell>
                            <TableCell class="font-medium">{{
                                student.name
                            }}</TableCell>
                            <TableCell>
                                <Badge variant="outline">{{
                                    student.institution.name
                                }}</Badge>
                            </TableCell>
                            <TableCell>
                                {{
                                    student.placements?.[0]?.classroom?.name ??
                                    '—'
                                }}
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :variant="student.domicile === 'kota_tangerang' ? 'default' : 'secondary'"
                                >
                                    {{ domicileLabel(student.domicile) }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :variant="student.is_active ? 'default' : 'destructive'"
                                >
                                    {{
                                        student.is_active ? 'Aktif' : 'Nonaktif'
                                    }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        @click="openEdit(student)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        @click="openDelete(student)"
                                    >
                                        <Trash2
                                            class="h-4 w-4 text-destructive"
                                        />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="students.data.length === 0">
                            <TableCell
                                colspan="8"
                                class="py-12 text-center text-muted-foreground"
                            >
                                Tidak ada siswa ditemukan.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div
                v-if="students.last_page > 1"
                class="mt-4 flex items-center justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Menampilkan {{ students.from }} - {{ students.to }} dari
                    {{ students.total }} siswa
                </p>
                <div class="flex gap-1">
                    <Button
                        v-for="link in students.links"
                        :key="link.label"
                        variant="outline"
                        size="sm"
                        class="h-8"
                        :disabled="!link.url || link.active"
                        as-child
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-state
                            v-html="link.label"
                        />
                        <span v-else v-html="link.label" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog v-model:open="showFormDialog">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingStudent ? 'Edit Siswa' : 'Tambah Siswa' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{
                            editingStudent
                                ? 'Perbarui data siswa'
                                : 'Isi data siswa baru'
                        }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitForm">
                    <div class="space-y-4 py-4">
                        <div class="grid gap-4 sm:grid-cols-2">
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
                            <div class="space-y-2">
                                <Label>Kelas</Label>
                                <Select v-model="form.classroom_id">
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder="Pilih Kelas"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="cls in filteredClassrooms"
                                            :key="cls.id"
                                            :value="String(cls.id)"
                                        >
                                            {{ cls.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="nis">NIS</Label>
                                <Input
                                    id="nis"
                                    v-model="form.nis"
                                    placeholder="Nomor Induk Siswa"
                                />
                                <p
                                    v-if="form.errors.nis"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.nis }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="nisn">NISN</Label>
                                <Input
                                    id="nisn"
                                    v-model="form.nisn"
                                    placeholder="Opsional"
                                />
                                <p
                                    v-if="form.errors.nisn"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.nisn }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="studentName">Nama Lengkap</Label>
                            <Input
                                id="studentName"
                                v-model="form.name"
                                placeholder="Nama lengkap siswa"
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label>Domisili</Label>
                            <Select v-model="form.domicile">
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Pilih Domisili"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="kota_tangerang"
                                        >Kota Tangerang</SelectItem
                                    >
                                    <SelectItem value="luar_kota_tangerang"
                                        >Luar Kota Tangerang</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <p
                                v-if="form.errors.domicile"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.domicile }}
                            </p>
                        </div>

                        <div
                            v-if="editingStudent"
                            class="flex items-center gap-2"
                        >
                            <Checkbox
                                id="is_active"
                                :checked="form.is_active"
                                @update:checked="form.is_active = $event as boolean"
                            />
                            <Label for="is_active">Siswa Aktif</Label>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="showFormDialog = false"
                        >
                            Batal
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ editingStudent ? 'Simpan' : 'Tambah' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Hapus Siswa</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus siswa
                        <strong>{{ deletingStudent?.name }}</strong
                        >? Tindakan ini tidak dapat dibatalkan.
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

        <!-- Sync Dialog -->
        <Dialog v-model:open="showSyncDialog">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Sinkronisasi Data Induk</DialogTitle>
                    <DialogDescription>
                        Tarik data siswa dari sistem Data Induk Yayasan. Siswa
                        yang sudah ada akan diperbarui, siswa baru akan
                        ditambahkan.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitSync">
                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label>Pilih Sumber Data</Label>
                            <Select v-model="syncForm.source">
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Pilih Instansi"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="siswa-mi"
                                        >MI</SelectItem
                                    >
                                    <SelectItem value="siswa-smp"
                                        >SMP</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <p
                                v-if="syncForm.errors.source"
                                class="text-sm text-destructive"
                            >
                                {{ syncForm.errors.source }}
                            </p>
                            <p
                                v-if="syncForm.errors.sync"
                                class="text-sm text-destructive"
                            >
                                {{ syncForm.errors.sync }}
                            </p>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="showSyncDialog = false"
                        >
                            Batal
                        </Button>
                        <Button type="submit" :disabled="syncForm.processing">
                            <RefreshCw
                                v-if="syncForm.processing"
                                class="mr-1 h-4 w-4 animate-spin"
                            />
                            {{ syncForm.processing ? 'Menyinkronkan...' : 'Mulai Sinkronisasi' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Bulk Delete Confirmation Dialog -->
        <Dialog v-model:open="showBulkDeleteDialog">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Hapus Siswa Terpilih</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus
                        <strong>{{ selectedIds.length }}</strong> siswa yang
                        dipilih? Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        variant="outline"
                        @click="showBulkDeleteDialog = false"
                    >
                        Batal
                    </Button>
                    <Button variant="destructive" @click="confirmBulkDelete">
                        Hapus {{ selectedIds.length }} Siswa
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
