<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Building2, Pencil, Plus, Trash2 } from 'lucide-vue-next';
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
            { title: 'Instansi', href: '/institutions' },
        ],
    },
});

type Institution = {
    id: number;
    name: string;
    code: string;
    students_count: number;
};

const props = defineProps<{
    institutions: Institution[];
}>();

const showFormDialog = ref(false);
const showDeleteDialog = ref(false);
const editingInstitution = ref<Institution | null>(null);
const deletingInstitution = ref<Institution | null>(null);

const form = useForm({
    name: '',
    code: '',
});

function openCreate() {
    editingInstitution.value = null;
    form.reset();
    form.clearErrors();
    showFormDialog.value = true;
}

function openEdit(institution: Institution) {
    editingInstitution.value = institution;
    form.clearErrors();
    form.name = institution.name;
    form.code = institution.code;
    showFormDialog.value = true;
}

function submitForm() {
    if (editingInstitution.value) {
        form.patch(`/institutions/${editingInstitution.value.id}`, {
            onSuccess: () => {
                showFormDialog.value = false;
            },
        });
    } else {
        form.post('/institutions', {
            onSuccess: () => {
                showFormDialog.value = false;
            },
        });
    }
}

function openDelete(institution: Institution) {
    deletingInstitution.value = institution;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (deletingInstitution.value) {
        router.delete(`/institutions/${deletingInstitution.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                deletingInstitution.value = null;
            },
        });
    }
}
</script>

<template>
    <Head title="Instansi" />

    <div class="space-y-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Instansi</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola instansi pendidikan yayasan
                </p>
            </div>
            <Button @click="openCreate">
                <Plus class="mr-1 h-4 w-4" /> Tambah Instansi
            </Button>
        </div>

        <div class="rounded-xl bg-background p-6 shadow-sm">
            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nama</TableHead>
                            <TableHead>Kode</TableHead>
                            <TableHead>Jumlah Siswa</TableHead>
                            <TableHead class="w-[100px]">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="institution in institutions"
                            :key="institution.id"
                        >
                            <TableCell class="font-medium">
                                <div class="flex items-center gap-2">
                                    <Building2 class="h-4 w-4 text-muted-foreground" />
                                    {{ institution.name }}
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">{{ institution.code }}</Badge>
                            </TableCell>
                            <TableCell>{{ institution.students_count }} siswa</TableCell>
                            <TableCell>
                                <div class="flex items-center gap-1">
                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEdit(institution)">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        v-if="institution.students_count === 0"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        @click="openDelete(institution)"
                                    >
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="institutions.length === 0">
                            <TableCell colspan="4" class="py-12 text-center text-muted-foreground">
                                Belum ada instansi.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog v-model:open="showFormDialog">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingInstitution ? 'Edit Instansi' : 'Tambah Instansi' }}
                    </DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitForm">
                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="instName">Nama Instansi</Label>
                            <Input id="instName" v-model="form.name" placeholder="Contoh: MI" />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="instCode">Kode</Label>
                            <Input id="instCode" v-model="form.code" placeholder="Contoh: mi" />
                            <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showFormDialog = false">Batal</Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ editingInstitution ? 'Simpan' : 'Tambah' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Hapus Instansi</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus instansi
                        <strong>{{ deletingInstitution?.name }}</strong>?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteDialog = false">Batal</Button>
                    <Button variant="destructive" @click="confirmDelete">Hapus</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
