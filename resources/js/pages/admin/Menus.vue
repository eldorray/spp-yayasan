<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Check,
    GripVertical,
    Menu,
    Pencil,
    Plus,
    Trash2,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type MenuItem = {
    id: number;
    title: string;
    url: string;
    icon: string;
    order: number;
    is_active: boolean;
    roles: string[];
    created_at: string;
};

type Props = {
    menus: MenuItem[];
    roles: string[];
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin' },
            { title: 'Menus', href: '/admin/menus' },
        ],
    },
});

// Create form
const showCreateForm = ref(false);
const createForm = useForm({
    title: '',
    url: '',
    icon: 'Circle',
    order: 0,
    is_active: true,
    roles: [] as string[],
});

function toggleCreateRole(role: string) {
    const index = createForm.roles.indexOf(role);
    if (index === -1) {
        createForm.roles.push(role);
    } else {
        createForm.roles.splice(index, 1);
    }
}

function submitCreate() {
    createForm.post('/admin/menus', {
        onSuccess: () => {
            createForm.reset();
            showCreateForm.value = false;
        },
    });
}

// Edit form
const editingId = ref<number | null>(null);
const editForm = useForm({
    title: '',
    url: '',
    icon: '',
    order: 0,
    is_active: true,
    roles: [] as string[],
});

function startEdit(menu: MenuItem) {
    editingId.value = menu.id;
    editForm.title = menu.title;
    editForm.url = menu.url;
    editForm.icon = menu.icon;
    editForm.order = menu.order;
    editForm.is_active = menu.is_active;
    editForm.roles = [...menu.roles];
}

function cancelEdit() {
    editingId.value = null;
    editForm.reset();
}

// Toggle role helper
function toggleEditRole(role: string) {
    const index = editForm.roles.indexOf(role);
    if (index === -1) {
        editForm.roles.push(role);
    } else {
        editForm.roles.splice(index, 1);
    }
}

function submitEdit(menu: MenuItem) {
    editForm.patch(`/admin/menus/${menu.id}`, {
        onSuccess: () => {
            editingId.value = null;
            editForm.reset();
        },
    });
}

function deleteMenu(menu: MenuItem) {
    if (confirm(`Are you sure you want to delete "${menu.title}"?`)) {
        router.delete(`/admin/menus/${menu.id}`);
    }
}

function toggleActive(menu: MenuItem) {
    router.patch(`/admin/menus/${menu.id}`, {
        title: menu.title,
        url: menu.url,
        icon: menu.icon,
        order: menu.order,
        is_active: !menu.is_active,
        roles: menu.roles,
    });
}
</script>

<template>
    <Head title="Menus - Admin" />

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col gap-1">
            <h1 class="text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">Navigation Menus</h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Structure sidebar navigation trees and delegate visibility boundaries
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
                    Menus Controller
                </div>
                <!-- Stats Indicator in Titlebar -->
                <div class="flex items-center gap-1.5 rounded-full bg-neutral-200/50 dark:bg-neutral-800/60 px-2.5 py-0.5 text-xs font-medium text-neutral-600 dark:text-neutral-300">
                    {{ menus.filter((m) => m.is_active).length }} / {{ menus.length }} Active
                </div>
            </div>

            <!-- Window Content -->
            <div class="p-6 space-y-6">
                <!-- Actions Panel -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="text-sm text-neutral-500 dark:text-neutral-400 flex items-center gap-2">
                        <Menu class="h-4 w-4 text-neutral-400" />
                        Configure dynamic sidebar nav panels and links
                    </div>

                    <Button class="tahoe-button-primary" @click="showCreateForm = !showCreateForm">
                        <Plus class="mr-1.5 h-4 w-4" /> New Menu
                    </Button>
                </div>

                <!-- Create Form inside Tahoe Card -->
                <div
                    v-if="showCreateForm"
                    class="rounded-xl border border-neutral-200/40 dark:border-neutral-800/40 p-5 bg-neutral-50/20 dark:bg-zinc-950/20 shadow-xs"
                >
                    <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-4">Add New Menu</h3>
                    <form class="space-y-4" @submit.prevent="submitCreate">
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="space-y-1.5">
                                <Label for="create-title" class="text-xs font-semibold text-neutral-500 dark:text-neutral-400">Title</Label>
                                <input
                                    id="create-title"
                                    v-model="createForm.title"
                                    placeholder="Menu title"
                                    required
                                    class="tahoe-input w-full"
                                />
                                <p
                                    v-if="createForm.errors.title"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ createForm.errors.title }}
                                </p>
                            </div>
                            <div class="space-y-1.5">
                                <Label for="create-url" class="text-xs font-semibold text-neutral-500 dark:text-neutral-400">URL</Label>
                                <input
                                    id="create-url"
                                    v-model="createForm.url"
                                    placeholder="/path/to/page"
                                    required
                                    class="tahoe-input w-full font-mono text-xs"
                                />
                                <p
                                    v-if="createForm.errors.url"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ createForm.errors.url }}
                                </p>
                            </div>
                            <div class="space-y-1.5">
                                <Label for="create-icon" class="text-xs font-semibold text-neutral-500 dark:text-neutral-400">Icon (Lucide name)</Label>
                                <input
                                    id="create-icon"
                                    v-model="createForm.icon"
                                    placeholder="e.g. FileText, Users"
                                    class="tahoe-input w-full"
                                />
                                <p
                                    v-if="createForm.errors.icon"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ createForm.errors.icon }}
                                </p>
                            </div>
                            <div class="space-y-1.5">
                                <Label for="create-order" class="text-xs font-semibold text-neutral-500 dark:text-neutral-400">Sort Order</Label>
                                <input
                                    id="create-order"
                                    v-model.number="createForm.order"
                                    type="number"
                                    min="0"
                                    class="tahoe-input w-full"
                                />
                            </div>
                        </div>

                        <!-- Roles visibility check -->
                        <div class="space-y-2">
                            <Label class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Visible to Roles (empty = visible to all)</Label>
                            <div class="flex flex-wrap gap-3">
                                <div
                                    v-for="role in roles"
                                    :key="role"
                                    class="flex items-center space-x-2.5 p-2 rounded-lg border border-neutral-100/50 dark:border-zinc-800/40 bg-white/70 dark:bg-zinc-900/60 hover:bg-neutral-50 dark:hover:bg-zinc-800 hover:border-neutral-200/80 dark:hover:border-zinc-700/60 transition-all duration-150 cursor-pointer shadow-xs"
                                    @click="toggleCreateRole(role)"
                                >
                                    <Checkbox
                                        :id="`create-role-${role}`"
                                        :checked="createForm.roles.includes(role)"
                                        @update:checked="toggleCreateRole(role)"
                                        @click.stop
                                    />
                                    <Label
                                        :for="`create-role-${role}`"
                                        class="font-medium text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer select-none"
                                    >
                                        {{ role }}
                                    </Label>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 border-t border-neutral-200/40 dark:border-neutral-800/40 pt-4">
                            <Button
                                type="submit"
                                :disabled="createForm.processing"
                                class="tahoe-button-primary"
                            >
                                Create Menu
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
                                <TableHead class="w-[70px] text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Order</TableHead>
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Title</TableHead>
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">URL Path</TableHead>
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Icon</TableHead>
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Visible To</TableHead>
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Status</TableHead>
                                <TableHead class="w-[100px] text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3 text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow 
                                v-for="menu in menus" 
                                :key="menu.id"
                                class="hover:bg-neutral-50/40 dark:hover:bg-zinc-900/30 border-b border-neutral-200/30 dark:border-neutral-800/30 transition-colors"
                            >
                                <!-- Inline Editing Mode -->
                                <template v-if="editingId === menu.id">
                                    <TableCell class="py-3">
                                        <input
                                            v-model.number="editForm.order"
                                            type="number"
                                            min="0"
                                            class="tahoe-input w-16 h-8 text-center"
                                        />
                                    </TableCell>
                                    <TableCell class="py-3">
                                        <input
                                            v-model="editForm.title"
                                            class="tahoe-input w-full h-8"
                                        />
                                    </TableCell>
                                    <TableCell class="py-3">
                                        <input
                                            v-model="editForm.url"
                                            class="tahoe-input w-full h-8 font-mono text-xs"
                                        />
                                    </TableCell>
                                    <TableCell class="py-3">
                                        <input
                                            v-model="editForm.icon"
                                            class="tahoe-input w-28 h-8"
                                        />
                                    </TableCell>
                                    <TableCell class="py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <div
                                                v-for="role in roles"
                                                :key="role"
                                                class="flex items-center space-x-1.5 cursor-pointer"
                                                @click="toggleEditRole(role)"
                                            >
                                                <Checkbox
                                                    :id="`edit-role-${role}`"
                                                    :checked="editForm.roles.includes(role)"
                                                    class="h-3.5 w-3.5"
                                                    @update:checked="toggleEditRole(role)"
                                                    @click.stop
                                                />
                                                <label
                                                    :for="`edit-role-${role}`"
                                                    class="text-xs font-medium text-neutral-600 dark:text-neutral-400 select-none cursor-pointer"
                                                >
                                                    {{ role }}
                                                </label>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell class="py-3">
                                        <Checkbox
                                            :checked="editForm.is_active"
                                            @update:checked="editForm.is_active = !editForm.is_active"
                                        />
                                    </TableCell>
                                    <TableCell class="py-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-8 w-8 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 hover:bg-neutral-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                                @click="submitEdit(menu)"
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
                                    </TableCell>
                                </template>

                                <!-- Display Mode -->
                                <template v-else>
                                    <TableCell class="py-3.5">
                                        <span
                                            class="flex items-center gap-1.5 font-semibold text-neutral-500 dark:text-neutral-400 text-xs"
                                        >
                                            <GripVertical class="h-3.5 w-3.5 text-neutral-400" />
                                            {{ menu.order }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="font-semibold text-neutral-800 dark:text-neutral-200 py-3.5">
                                        {{ menu.title }}
                                    </TableCell>
                                    <TableCell class="py-3.5 font-mono text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ menu.url }}
                                    </TableCell>
                                    <TableCell class="py-3.5">
                                        <span class="font-medium text-xs bg-neutral-100 dark:bg-zinc-800 text-neutral-700 dark:text-neutral-300 px-2 py-0.5 rounded-md border border-neutral-200/30 dark:border-zinc-700/30">
                                            {{ menu.icon }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="py-3.5">
                                        <div class="flex flex-wrap gap-1">
                                            <Badge
                                                v-for="role in menu.roles"
                                                :key="role"
                                                variant="secondary"
                                                class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-violet-50 text-violet-700 border-violet-200/50 dark:bg-violet-950/20 dark:text-violet-300 dark:border-violet-900/50"
                                            >
                                                {{ role }}
                                            </Badge>
                                            <span
                                                v-if="menu.roles.length === 0"
                                                class="text-xs text-neutral-400 dark:text-neutral-500 italic"
                                            >
                                                All roles (Guest/Auth)
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="py-3.5">
                                        <Badge
                                            :class="[
                                                'rounded-full px-2.5 py-0.5 text-xs font-semibold border cursor-pointer select-none transition-colors duration-150',
                                                menu.is_active
                                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200/50 dark:bg-emerald-950/20 dark:text-emerald-300 dark:border-emerald-900/50'
                                                    : 'bg-neutral-100 text-neutral-500 border-neutral-200 dark:bg-zinc-800 dark:text-neutral-400 dark:border-zinc-700'
                                            ]"
                                            @click="toggleActive(menu)"
                                        >
                                            {{ menu.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-8 w-8 text-neutral-500 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                                @click="startEdit(menu)"
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-8 w-8 text-neutral-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors"
                                                @click="deleteMenu(menu)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </template>
                            </TableRow>
                            <TableRow v-if="menus.length === 0">
                                <TableCell
                                    colspan="7"
                                    class="py-12 text-center text-neutral-400 dark:text-neutral-500 italic"
                                >
                                    No dynamic menus configured.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
            
            <!-- Window Footer with Tips -->
            <div class="px-6 py-4 bg-neutral-50/40 dark:bg-zinc-950/10 border-t border-neutral-200/40 dark:border-neutral-800/40 flex items-center justify-between text-xs text-neutral-400 dark:text-neutral-500">
                <span>
                    💡 Icon keys map to Lucide PascalCase classes (e.g. <code>FileText</code>, <code>Users</code>). Refer to <a href="https://lucide.dev/icons" target="_blank" class="underline hover:text-neutral-700 dark:hover:text-neutral-200">lucide.dev</a>.
                </span>
                <span>
                    *Empty roles grant global authorization
                </span>
            </div>
        </div>
    </div>
</template>
