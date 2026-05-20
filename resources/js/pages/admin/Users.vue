<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Search, Trash2, Users } from 'lucide-vue-next';
import { ref } from 'vue';
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
import type { PaginatedData, AdminUser } from '@/types/admin';

type Props = {
    users: PaginatedData<AdminUser>;
    filters: {
        search: string | null;
    };
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin' },
            { title: 'Users', href: '/admin/users' },
        ],
    },
});

const search = ref(props.filters.search ?? '');

function handleSearch() {
    router.get(
        '/admin/users',
        { search: search.value || undefined },
        { preserveState: true, replace: true },
    );
}

function deleteUser(user: AdminUser) {
    if (confirm(`Are you sure you want to delete "${user.name}"?`)) {
        router.delete(`/admin/users/${user.id}`);
    }
}
</script>

<template>
    <Head title="Users - Admin" />

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col gap-1">
            <h1 class="text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">System Administration</h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Manage user permissions, security roles, and system menus.
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
                    Users Manager
                </div>
                <!-- Stats Indicator in Titlebar -->
                <div class="flex items-center gap-1.5 rounded-full bg-neutral-200/50 dark:bg-neutral-800/60 px-2.5 py-0.5 text-xs font-medium text-neutral-600 dark:text-neutral-300">
                    {{ users.total }} Active Users
                </div>
            </div>

            <!-- Window Content -->
            <div class="p-6 space-y-6">
                <!-- Search & Actions Panel -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <form
                        class="relative w-full sm:max-w-xs"
                        @submit.prevent="handleSearch"
                    >
                        <Search
                            class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400 dark:text-neutral-500"
                        />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search users..."
                            class="w-full pl-9 tahoe-input"
                            @keyup.enter="handleSearch"
                        />
                    </form>

                    <Button as-child class="tahoe-button-primary">
                        <Link href="/admin/users/create">
                            <Plus class="mr-1.5 h-4 w-4" /> New User
                        </Link>
                    </Button>
                </div>

                <!-- Tahoe Styled Table -->
                <div class="rounded-xl border border-neutral-200/40 dark:border-neutral-800/40 overflow-hidden bg-white/40 dark:bg-zinc-950/20 backdrop-blur-md">
                    <Table>
                        <TableHeader class="bg-neutral-50/50 dark:bg-zinc-900/50">
                            <TableRow class="border-b border-neutral-200/40 dark:border-neutral-800/40">
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Name</TableHead>
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Email</TableHead>
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Roles</TableHead>
                                <TableHead class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3">Created</TableHead>
                                <TableHead class="w-[100px] text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 py-3 text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow 
                                v-for="user in users.data" 
                                :key="user.id"
                                class="hover:bg-neutral-50/40 dark:hover:bg-zinc-900/30 border-b border-neutral-200/30 dark:border-neutral-800/30 transition-colors"
                            >
                                <TableCell class="font-semibold text-neutral-800 dark:text-neutral-200 py-3.5">
                                    {{ user.name }}
                                </TableCell>
                                <TableCell class="text-neutral-500 dark:text-neutral-400 py-3.5">
                                    {{ user.email }}
                                </TableCell>
                                <TableCell class="py-3.5">
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="role in user.roles"
                                            :key="role"
                                            variant="secondary"
                                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-violet-100 text-violet-700 dark:bg-violet-950/40 dark:text-violet-300 border border-violet-200/30 dark:border-violet-800/30"
                                        >
                                            {{ role }}
                                        </Badge>
                                        <span
                                            v-if="user.roles.length === 0"
                                            class="text-xs text-neutral-400 dark:text-neutral-500 italic"
                                        >
                                            No roles assigned
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell class="text-xs text-neutral-400 dark:text-neutral-500 py-3.5">
                                    {{
                                        new Date(
                                            user.created_at,
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
                                                :href="`/admin/users/${user.id}/edit`"
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-neutral-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors"
                                            @click="deleteUser(user)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="users.data.length === 0">
                                <TableCell
                                    colspan="5"
                                    class="py-12 text-center text-neutral-400 dark:text-neutral-500 italic"
                                >
                                    No users found matching your search.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Tahoe Styled Pagination -->
                <div
                    v-if="users.last_page > 1"
                    class="flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-neutral-200/40 dark:border-neutral-800/40 pt-4"
                >
                    <p class="text-xs text-neutral-400 dark:text-neutral-500">
                        Showing {{ users.from }} to {{ users.to }} of
                        {{ users.total }} users
                    </p>
                    <div class="flex gap-1">
                        <Button
                            v-for="link in users.links"
                            :key="link.label"
                            variant="outline"
                            size="sm"
                            class="h-8 rounded-lg text-xs transition-colors hover:bg-neutral-100 dark:hover:bg-zinc-800"
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
        </div>
    </div>
</template>
