<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Check, KeyRound, Pencil, Plus, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { AdminPermission } from '@/types/admin';

type Props = {
    permissions: AdminPermission[];
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin' },
            { title: 'Permissions', href: '/admin/permissions' },
        ],
    },
});

const showCreateForm = ref(false);
const editingId = ref<number | null>(null);
const editName = ref('');

const createForm = useForm({
    name: '',
});

function submitCreate() {
    createForm.post('/admin/permissions', {
        onSuccess: () => {
            createForm.reset();
            showCreateForm.value = false;
        },
    });
}

function startEdit(permission: AdminPermission) {
    editingId.value = permission.id;
    editName.value = permission.name;
}

function cancelEdit() {
    editingId.value = null;
    editName.value = '';
}

function submitEdit(permission: AdminPermission) {
    router.patch(
        `/admin/permissions/${permission.id}`,
        { name: editName.value },
        {
            onSuccess: () => {
                editingId.value = null;
                editName.value = '';
            },
        },
    );
}

function deletePermission(permission: AdminPermission) {
    if (
        confirm(
            `Are you sure you want to delete the permission "${permission.name}"?`,
        )
    ) {
        router.delete(`/admin/permissions/${permission.id}`);
    }
}
</script>

<template>
    <Head title="Permissions - Admin" />

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col gap-1">
            <h1 class="text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">Permissions Registry</h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Grant access tokens and map endpoint policies across components
            </p>
        </div>

        <!-- Tahoe macOS Window -->
        <div class="tahoe-window">
            <!-- Window Title Bar -->
            <div class="tahoe-title-bar">
                <div class="flex items-center gap-2">
                    <!-- Traffic Lights simulated controls -->
                    <div class="flex gap-1.5">
                        <span class="h-3 w-3 rounded-full bg-[#FF5F56] border border-[#E0443E]"></span>
                        <span class="h-3 w-3 rounded-full bg-[#FFBD2E] border border-[#DEA123]"></span>
                        <span class="h-3 w-3 rounded-full bg-[#27C93F] border border-[#1AAB29]"></span>
                    </div>
                    <span class="ml-2 text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">Finder</span>
                </div>
                <div class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">
                    Permissions Manager
                </div>
                <!-- Stats Indicator in Titlebar -->
                <div class="flex items-center gap-1.5 rounded-full bg-neutral-200/50 dark:bg-neutral-800/60 px-2.5 py-0.5 text-xs font-medium text-neutral-600 dark:text-neutral-300">
                    {{ permissions.length }} Total Permissions
                </div>
            </div>

            <!-- Window Content -->
            <div class="p-6 space-y-6">
                <!-- Actions Panel -->
                <div class="flex items-center justify-between gap-4">
                    <div class="text-sm text-neutral-500 dark:text-neutral-400 flex items-center gap-2">
                        <KeyRound class="h-4 w-4 text-neutral-400" />
                        Manage policy actions and endpoint boundaries
                    </div>

                    <Button class="tahoe-button-primary" @click="showCreateForm = !showCreateForm">
                        <Plus class="mr-1.5 h-4 w-4" /> New Permission
                    </Button>
                </div>

                <!-- Create form inside Tahoe Card -->
                <div 
                    v-if="showCreateForm"
                    class="rounded-xl border border-neutral-200/40 dark:border-neutral-800/40 p-4 bg-neutral-50/20 dark:bg-zinc-950/20 shadow-xs"
                >
                    <form
                        class="flex flex-col sm:flex-row items-end gap-3"
                        @submit.prevent="submitCreate"
                    >
                        <div class="flex-1 space-y-1.5 w-full">
                            <Label for="new-perm-name" class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Permission Key Name</Label>
                            <input
                                id="new-perm-name"
                                v-model="createForm.name"
                                type="text"
                                placeholder="e.g. users.create, payments.verify"
                                required
                                class="tahoe-input w-full"
                            />
                            <p
                                v-if="createForm.errors.name"
                                class="text-xs text-red-500 mt-1"
                            >
                                {{ createForm.errors.name }}
                            </p>
                        </div>
                        <div class="flex gap-2 w-full sm:w-auto justify-end">
                            <Button type="submit" :disabled="createForm.processing" class="tahoe-button-primary">
                                Create
                            </Button>
                            <Button
                                type="button"
                                class="tahoe-button-secondary"
                                @click="showCreateForm = false"
                            >
                                Cancel
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- Tahoe Styled Table -->
                <div class="rounded-xl border border-neutral-200/40 dark:border-neutral-800/40 overflow-hidden bg-white/40 dark:bg-zinc-950/20 backdrop-blur-md">
                    <Table>
                        <TableHeader class="bg-neutral-50/50 dark:bg-zinc-900/50">
                            <TableRow class="border-b border-neutral-200/40 dark:border-neutral-800/40">
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Permission Name Key</TableHead>
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Created</TableHead>
                                <TableHead class="w-[100px] text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3 text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="permission in permissions"
                                :key="permission.id"
                                class="hover:bg-neutral-50/40 dark:hover:bg-zinc-900/30 border-b border-neutral-200/30 dark:border-neutral-800/30 transition-colors"
                            >
                                <TableCell class="py-3.5">
                                    <div
                                        v-if="editingId === permission.id"
                                        class="flex items-center gap-2"
                                    >
                                        <input
                                            v-model="editName"
                                            class="tahoe-input w-full max-w-xs h-9"
                                            @keyup.enter="submitEdit(permission)"
                                            @keyup.escape="cancelEdit"
                                        />
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 hover:bg-neutral-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                            @click="submitEdit(permission)"
                                        >
                                            <Check class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-neutral-500 hover:text-neutral-700 hover:bg-neutral-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                            @click="cancelEdit"
                                        >
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                    <span v-else class="font-mono text-xs font-semibold bg-neutral-100 dark:bg-zinc-800 text-neutral-700 dark:text-neutral-300 px-2 py-1 rounded-md border border-neutral-200/30 dark:border-zinc-700/30">
                                        {{ permission.name }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-xs text-neutral-400 dark:text-neutral-500 py-3.5">
                                    {{
                                        new Date(
                                            permission.created_at,
                                        ).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
                                    }}
                                </TableCell>
                                <TableCell class="py-3.5 text-right">
                                    <div
                                        v-if="editingId !== permission.id"
                                        class="flex items-center justify-end gap-1.5"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-neutral-500 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                            @click="startEdit(permission)"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-neutral-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors"
                                            @click="deletePermission(permission)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="permissions.length === 0">
                                <TableCell
                                    colspan="3"
                                    class="py-12 text-center text-neutral-400 dark:text-neutral-500 italic"
                                >
                                    No system permissions registered.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </div>
</template>
