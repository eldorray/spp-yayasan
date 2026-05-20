<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, School, Trash2 } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
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
            { title: 'Kelas', href: '/classrooms' },
        ],
    },
});

type Classroom = {
    id: number;
    name: string;
    is_active: boolean;
    institution: { id: number; name: string; code: string };
    academic_year: { id: number; name: string };
    student_placements_count: number;
};

type Institution = { id: number; name: string; code: string };
type AcademicYear = { id: number; name: string; is_active: boolean };

const props = defineProps<{
    classrooms: Classroom[];
    institutions: Institution[];
    academicYears: AcademicYear[];
    activeYearId: number;
    filters: Record<string, string>;
}>();

const showDialog = ref(false);
const showDeleteDialog = ref(false);
const showBulkDeleteDialog = ref(false);
const editingClassroom = ref<Classroom | null>(null);
const deletingClassroom = ref<Classroom | null>(null);

// Bulk selection
const selectedIds = ref<number[]>([]);
const headerCheckbox = ref<HTMLInputElement | null>(null);

const deletableClassrooms = computed(() =>
    props.classrooms.filter((c) => c.student_placements_count === 0),
);

const isAllSelected = computed(() => {
    return (
        deletableClassrooms.value.length > 0 &&
        deletableClassrooms.value.every((c) => selectedIds.value.includes(c.id))
    );
});

const isSomeSelected = computed(() => {
    return selectedIds.value.length > 0 && !isAllSelected.value;
});

watch([isAllSelected, isSomeSelected], () => {
    if (headerCheckbox.value) {
        headerCheckbox.value.indeterminate = isSomeSelected.value;
    }
});

function toggleAll(checked: boolean) {
    if (checked) {
        selectedIds.value = deletableClassrooms.value.map((c) => c.id);
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
        '/classrooms/bulk-delete',
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
    academic_year_id: String(props.activeYearId),
    institution_id: '',
    name: '',
    is_active: true,
});

function openCreate() {
    editingClassroom.value = null;
    form.academic_year_id = String(props.activeYearId);
    form.institution_id = '';
    form.name = '';
    showDialog.value = true;
}

function openEdit(classroom: Classroom) {
    editingClassroom.value = classroom;
    form.name = classroom.name;
    form.is_active = classroom.is_active;
    showDialog.value = true;
}

function submit() {
    if (editingClassroom.value) {
        form.patch(`/classrooms/${editingClassroom.value.id}`, {
            onSuccess: () => {
                showDialog.value = false;
            },
        });
    } else {
        form.post('/classrooms', {
            onSuccess: () => {
                showDialog.value = false;
            },
        });
    }
}

function openDelete(classroom: Classroom) {
    deletingClassroom.value = classroom;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (deletingClassroom.value) {
        router.delete(`/classrooms/${deletingClassroom.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                deletingClassroom.value = null;
            },
        });
    }
}

function filterByInstitution(value: string | null) {
    router.get(
        '/classrooms',
        {
            institution_id: !value || value === 'all' ? undefined : value,
            academic_year_id: props.activeYearId || undefined,
        },
        { preserveState: true, replace: true },
    );
}
</script>

<template>
    <Head title="Kelas" />

    <div class="space-y-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Kelas</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola kelas per tahun ajaran dan instansi
                </p>
            </div>
            <Button @click="openCreate">
                <Plus class="mr-1 h-4 w-4" /> Tambah Kelas
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

        <!-- Bulk Action Bar -->
        <div
            v-if="selectedIds.length > 0"
            class="flex items-center gap-3 rounded-lg border border-destructive/20 bg-destructive/5 px-4 py-3"
        >
            <span class="text-sm font-medium">
                {{ selectedIds.length }} kelas dipilih
            </span>
            <Button variant="destructive" size="sm" @click="openBulkDelete">
                <Trash2 class="mr-1 h-4 w-4" /> Hapus Terpilih
            </Button>
            <Button variant="ghost" size="sm" @click="selectedIds = []">
                Batal Pilih
            </Button>
        </div>

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
                                    :disabled="deletableClassrooms.length === 0"
                                    @change="toggleAll(($event.target as HTMLInputElement).checked)"
                                />
                            </TableHead>
                            <TableHead>Nama Kelas</TableHead>
                            <TableHead>Instansi</TableHead>
                            <TableHead>Jumlah Siswa</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="w-[100px]">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="classroom in classrooms"
                            :key="classroom.id"
                        >
                            <TableCell>
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-input accent-primary"
                                    :checked="selectedIds.includes(classroom.id)"
                                    :disabled="classroom.student_placements_count > 0"
                                    @change="toggleOne(classroom.id, ($event.target as HTMLInputElement).checked)"
                                />
                            </TableCell>
                            <TableCell class="font-medium">
                                <div class="flex items-center gap-2">
                                    <School class="h-4 w-4 text-muted-foreground" />
                                    {{ classroom.name }}
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">
                                    {{ classroom.institution.name }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                {{ classroom.student_placements_count }} siswa
                            </TableCell>
                            <TableCell>
                                <Badge :variant="classroom.is_active ? 'default' : 'secondary'">
                                    {{ classroom.is_active ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-1">
                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEdit(classroom)">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        v-if="classroom.student_placements_count === 0"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        @click="openDelete(classroom)"
                                    >
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="classrooms.length === 0">
                            <TableCell colspan="6" class="py-12 text-center text-muted-foreground">
                                Belum ada kelas.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog v-model:open="showDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ editingClassroom ? 'Edit Kelas' : 'Tambah Kelas' }}
                    </DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit">
                    <div class="space-y-4 py-4">
                        <div v-if="!editingClassroom" class="space-y-2">
                            <Label>Instansi</Label>
                            <Select v-model="form.institution_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Instansi" />
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
                        </div>
                        <div class="space-y-2">
                            <Label for="className">Nama Kelas</Label>
                            <Input
                                id="className"
                                v-model="form.name"
                                placeholder="Contoh: Kelas 1A"
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showDialog = false">
                            Batal
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ editingClassroom ? 'Simpan' : 'Tambah' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Hapus Kelas</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus kelas
                        <strong>{{ deletingClassroom?.name }}</strong>?
                        Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteDialog = false">
                        Batal
                    </Button>
                    <Button variant="destructive" @click="confirmDelete">
                        Hapus
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Bulk Delete Confirmation Dialog -->
        <Dialog v-model:open="showBulkDeleteDialog">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Hapus Kelas Terpilih</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus
                        <strong>{{ selectedIds.length }}</strong> kelas yang
                        dipilih? Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showBulkDeleteDialog = false">
                        Batal
                    </Button>
                    <Button variant="destructive" @click="confirmBulkDelete">
                        Hapus {{ selectedIds.length }} Kelas
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
