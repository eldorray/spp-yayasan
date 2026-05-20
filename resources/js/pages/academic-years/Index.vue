<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Calendar, CheckCircle, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
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
            { title: 'Tahun Ajaran', href: '/academic-years' },
        ],
    },
});

type AcademicYear = {
    id: number;
    name: string;
    is_active: boolean;
    created_at: string;
};

const props = defineProps<{
    academicYears: AcademicYear[];
}>();

const showDialog = ref(false);
const showDeleteDialog = ref(false);
const showActivateDialog = ref(false);
const editingYear = ref<AcademicYear | null>(null);
const deletingYear = ref<AcademicYear | null>(null);
const activatingYear = ref<AcademicYear | null>(null);

const form = useForm({ name: '' });

function openCreate() {
    editingYear.value = null;
    form.name = '';
    showDialog.value = true;
}

function openEdit(year: AcademicYear) {
    editingYear.value = year;
    form.name = year.name;
    showDialog.value = true;
}

function submit() {
    if (editingYear.value) {
        form.patch(`/academic-years/${editingYear.value.id}`, {
            onSuccess: () => {
                showDialog.value = false;
            },
        });
    } else {
        form.post('/academic-years', {
            onSuccess: () => {
                showDialog.value = false;
            },
        });
    }
}

function openActivate(year: AcademicYear) {
    activatingYear.value = year;
    showActivateDialog.value = true;
}

function confirmActivate() {
    if (activatingYear.value) {
        router.post(`/academic-years/${activatingYear.value.id}/activate`, {}, {
            onSuccess: () => {
                showActivateDialog.value = false;
                activatingYear.value = null;
            },
        });
    }
}

function openDelete(year: AcademicYear) {
    deletingYear.value = year;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (deletingYear.value) {
        router.delete(`/academic-years/${deletingYear.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                deletingYear.value = null;
            },
        });
    }
}
</script>

<template>
    <Head title="Tahun Ajaran" />

    <div class="space-y-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Tahun Ajaran</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola tahun ajaran untuk data operasional
                </p>
            </div>
            <Button @click="openCreate">
                <Plus class="mr-1 h-4 w-4" /> Tambah Tahun Ajaran
            </Button>
        </div>

        <div class="rounded-xl bg-background p-6 shadow-sm">
            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nama</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="w-[200px]">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="year in academicYears"
                            :key="year.id"
                        >
                            <TableCell class="font-medium">
                                <div class="flex items-center gap-2">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                    {{ year.name }}
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :variant="year.is_active ? 'default' : 'secondary'"
                                >
                                    {{ year.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-1">
                                    <Button
                                        v-if="!year.is_active"
                                        variant="ghost"
                                        size="sm"
                                        @click="openActivate(year)"
                                    >
                                        <CheckCircle class="mr-1 h-4 w-4" />
                                        Aktifkan
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        @click="openEdit(year)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        v-if="!year.is_active"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        @click="openDelete(year)"
                                    >
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="academicYears.length === 0">
                            <TableCell colspan="3" class="py-12 text-center text-muted-foreground">
                                Belum ada tahun ajaran.
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
                        {{ editingYear ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran' }}
                    </DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit">
                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="name">Nama Tahun Ajaran</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Contoh: 2026/2027"
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
                            {{ editingYear ? 'Simpan' : 'Tambah' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Activate Confirmation Dialog -->
        <Dialog v-model:open="showActivateDialog">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Aktifkan Tahun Ajaran</DialogTitle>
                    <DialogDescription>
                        Aktifkan tahun ajaran <strong>{{ activatingYear?.name }}</strong>?
                        Tahun ajaran lain akan dinonaktifkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showActivateDialog = false">
                        Batal
                    </Button>
                    <Button @click="confirmActivate">
                        Aktifkan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Hapus Tahun Ajaran</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus tahun ajaran
                        <strong>{{ deletingYear?.name }}</strong>?
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
    </div>
</template>
