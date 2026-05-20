<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Shield, Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { AdminRole } from '@/types/admin';

type Props = {
    roles: AdminRole[];
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin' },
            { title: 'Roles', href: '/admin/roles' },
        ],
    },
});

function deleteRole(role: AdminRole) {
    if (confirm(`Are you sure you want to delete the role "${role.name}"?`)) {
        router.delete(`/admin/roles/${role.id}`);
    }
}
</script>

<template>
    <Head title="Roles - Admin" />

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col gap-1">
            <h1 class="text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">Role Management</h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Define security roles and delegate access control policies
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
                    Roles & Permissions
                </div>
                <!-- Stats Indicator in Titlebar -->
                <div class="flex items-center gap-1.5 rounded-full bg-neutral-200/50 dark:bg-neutral-800/60 px-2.5 py-0.5 text-xs font-medium text-neutral-600 dark:text-neutral-300">
                    {{ roles.length }} Total Roles
                </div>
            </div>

            <!-- Window Content -->
            <div class="p-6 space-y-6">
                <!-- Actions Panel -->
                <div class="flex items-center justify-between gap-4">
                    <div class="text-sm text-neutral-500 dark:text-neutral-400 flex items-center gap-2">
                        <Shield class="h-4 w-4 text-neutral-400" />
                        Configure scope permissions and client levels
                    </div>

                    <Button as-child class="tahoe-button-primary">
                        <Link href="/admin/roles/create">
                            <Plus class="mr-1.5 h-4 w-4" /> New Role
                        </Link>
                    </Button>
                </div>

                <!-- Tahoe Styled Table -->
                <div class="rounded-xl border border-neutral-200/40 dark:border-neutral-800/40 overflow-hidden bg-white/40 dark:bg-zinc-950/20 backdrop-blur-md">
                    <Table>
                        <TableHeader class="bg-neutral-50/50 dark:bg-zinc-900/50">
                            <TableRow class="border-b border-neutral-200/40 dark:border-neutral-800/40">
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Role Name</TableHead>
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Permissions</TableHead>
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Assigned Users</TableHead>
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Created</TableHead>
                                <TableHead class="w-[100px] text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3 text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow 
                                v-for="role in roles" 
                                :key="role.id"
                                class="hover:bg-neutral-50/40 dark:hover:bg-zinc-900/30 border-b border-neutral-200/30 dark:border-neutral-800/30 transition-colors"
                            >
                                <TableCell class="font-semibold text-neutral-800 dark:text-neutral-200 py-3.5">
                                    {{ role.name }}
                                </TableCell>
                                <TableCell class="py-3.5">
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="permission in role.permissions.slice(0, 4)"
                                            :key="permission"
                                            variant="outline"
                                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-violet-50 text-violet-700 border-violet-200/50 dark:bg-violet-950/20 dark:text-violet-300 dark:border-violet-900/50"
                                        >
                                            {{ permission }}
                                        </Badge>
                                        <Badge
                                            v-if="role.permissions.length > 4"
                                            variant="secondary"
                                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-50 text-amber-700 border-amber-200/50 dark:bg-amber-950/20 dark:text-amber-300 dark:border-amber-900/50"
                                        >
                                            +{{ role.permissions.length - 4 }} more
                                        </Badge>
                                        <span
                                            v-if="role.permissions.length === 0"
                                            class="text-xs text-neutral-400 dark:text-neutral-500 italic"
                                        >
                                            No permissions assigned
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell class="py-3.5">
                                    <Badge 
                                        variant="secondary" 
                                        class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-50 text-emerald-700 border-emerald-200/50 dark:bg-emerald-950/20 dark:text-emerald-300 dark:border-emerald-900/50"
                                    >
                                        {{ role.users_count }} users
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-xs text-neutral-400 dark:text-neutral-500 py-3.5">
                                    {{
                                        new Date(
                                            role.created_at,
                                        ).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
                                    }}
                                </TableCell>
                                <TableCell class="py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-neutral-500 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                            as-child
                                        >
                                            <Link
                                                :href="`/admin/roles/${role.id}/edit`"
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-neutral-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors"
                                            :disabled="role.name === 'super-admin'"
                                            @click="deleteRole(role)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="roles.length === 0">
                                <TableCell
                                    colspan="5"
                                    class="py-12 text-center text-neutral-400 dark:text-neutral-500 italic"
                                >
                                    No roles created yet.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </div>
</template>
