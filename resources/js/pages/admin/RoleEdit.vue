<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

type Props = {
    role: {
        id: number;
        name: string;
        permissions: string[];
    };
    permissions: string[];
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin' },
            { title: 'Roles', href: '/admin/roles' },
            { title: 'Edit', href: '#' },
        ],
    },
});

const form = useForm({
    name: props.role.name,
    permissions: [...props.role.permissions],
});

function togglePermission(permission: string) {
    const index = form.permissions.indexOf(permission);
    if (index === -1) {
        form.permissions.push(permission);
    } else {
        form.permissions.splice(index, 1);
    }
}

function selectAll() {
    form.permissions = [...props.permissions];
}

function deselectAll() {
    form.permissions = [];
}

function submit() {
    form.patch(`/admin/roles/${props.role.id}`);
}
</script>

<template>
    <Head title="Edit Role - Admin" />

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex items-center gap-3">
            <Button variant="ghost" size="icon" class="h-8 w-8 text-neutral-500 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-zinc-800 rounded-lg transition-colors" as-child>
                <Link href="/admin/roles">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div class="flex flex-col gap-0.5">
                <h1 class="text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">Edit Role</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Modifying security properties for "{{ role.name }}"
                </p>
            </div>
        </div>

        <!-- Tahoe macOS Window -->
        <div class="max-w-2xl tahoe-window">
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
                    Role Editor
                </div>
                <div class="w-12"></div>
            </div>

            <!-- Window Content -->
            <div class="p-6">
                <form class="space-y-6" @submit.prevent="submit">
                    <!-- Role Name Input -->
                    <div class="space-y-2 flex flex-col">
                        <Label for="name" class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Role Name</Label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                            :disabled="role.name === 'super-admin'"
                            placeholder="e.g. editor, moderator"
                            class="tahoe-input w-full disabled:opacity-50 disabled:cursor-not-allowed"
                        />
                        <p
                            v-if="form.errors.name"
                            class="text-xs text-red-500 mt-1"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Permissions Selection Grid -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <Label class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">System Permissions</Label>
                            <div class="flex gap-2">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white"
                                    @click="selectAll"
                                >
                                    Select All
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white"
                                    @click="deselectAll"
                                >
                                    Deselect All
                                </Button>
                            </div>
                        </div>

                        <!-- Checkbox Wrapper Container -->
                        <div
                            class="grid max-h-72 gap-3 overflow-y-auto rounded-xl border border-neutral-200/40 dark:border-neutral-800/40 p-4 bg-neutral-50/20 dark:bg-zinc-950/20 sm:grid-cols-2"
                        >
                            <div
                                v-for="permission in permissions"
                                :key="permission"
                                class="flex items-center space-x-3 p-2.5 rounded-lg border border-neutral-100/50 dark:border-zinc-800/40 bg-white/70 dark:bg-zinc-900/60 hover:bg-neutral-50 dark:hover:bg-zinc-800 hover:border-neutral-200/80 dark:hover:border-zinc-700/60 transition-all duration-200 shadow-xs cursor-pointer"
                                @click="togglePermission(permission)"
                            >
                                <Checkbox
                                    :id="`perm-${permission}`"
                                    :checked="form.permissions.includes(permission)"
                                    @update:checked="togglePermission(permission)"
                                    @click.stop
                                />
                                <Label :for="`perm-${permission}`" class="font-medium text-sm text-neutral-700 dark:text-neutral-300 cursor-pointer select-none">
                                    {{ permission }}
                                </Label>
                            </div>
                            <p
                                v-if="permissions.length === 0"
                                class="col-span-2 py-6 text-center text-sm text-neutral-400 dark:text-neutral-500 italic"
                            >
                                No permissions available.
                            </p>
                        </div>
                        <p
                            v-if="form.errors.permissions"
                            class="text-xs text-red-500 mt-1"
                        >
                            {{ form.errors.permissions }}
                        </p>
                    </div>

                    <!-- Actions Form footer -->
                    <div class="flex items-center gap-3 border-t border-neutral-200/40 dark:border-neutral-800/40 pt-6">
                        <Button type="submit" :disabled="form.processing" class="tahoe-button-primary">
                            Update Role
                        </Button>
                        <Button as-child class="tahoe-button-secondary">
                            <Link href="/admin/roles">Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
