<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

type Props = {
    roles: string[];
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin' },
            { title: 'Users', href: '/admin/users' },
            { title: 'Create', href: '/admin/users/create' },
        ],
    },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    roles: [] as string[],
});

function toggleRole(role: string) {
    const index = form.roles.indexOf(role);
    if (index === -1) {
        form.roles.push(role);
    } else {
        form.roles.splice(index, 1);
    }
}

function submit() {
    form.post('/admin/users');
}
</script>

<template>
    <Head title="Create User - Admin" />

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex items-center gap-3">
            <Button variant="ghost" size="icon" class="h-8 w-8 text-neutral-500 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-zinc-800 rounded-lg transition-colors" as-child>
                <Link href="/admin/users">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div class="flex flex-col gap-0.5">
                <h1 class="text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">Create User</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Add a new user account with specific permissions
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
                    New User Profile
                </div>
                <div class="w-12"></div>
            </div>

            <!-- Window Content -->
            <div class="p-6">
                <form class="space-y-6" @submit.prevent="submit">
                    <!-- Form Fields Grid -->
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="space-y-2 flex flex-col">
                            <Label for="name" class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Name</Label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                autofocus
                                placeholder="Full name"
                                class="tahoe-input w-full"
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-xs text-red-500 mt-1"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2 flex flex-col">
                            <Label for="email" class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Email Address</Label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="email@example.com"
                                class="tahoe-input w-full"
                            />
                            <p
                                v-if="form.errors.email"
                                class="text-xs text-red-500 mt-1"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2 flex flex-col">
                        <Label for="password" class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Password</Label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            placeholder="Minimum 8 characters"
                            class="tahoe-input w-full"
                        />
                        <p
                            v-if="form.errors.password"
                            class="text-xs text-red-500 mt-1"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Roles Select Box -->
                    <div class="space-y-3">
                        <Label class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Assign Roles</Label>
                        <div
                            class="grid gap-3 rounded-xl border border-neutral-200/40 dark:border-neutral-800/40 p-4 bg-neutral-50/20 dark:bg-zinc-950/20 sm:grid-cols-2"
                        >
                            <div
                                v-for="role in roles"
                                :key="role"
                                class="flex items-center space-x-3 p-2.5 rounded-lg border border-neutral-100/50 dark:border-zinc-800/40 bg-white/70 dark:bg-zinc-900/60 hover:bg-neutral-50 dark:hover:bg-zinc-800 hover:border-neutral-200/80 dark:hover:border-zinc-700/60 transition-all duration-200 shadow-xs cursor-pointer"
                                @click="toggleRole(role)"
                            >
                                <Checkbox
                                    :id="`role-${role}`"
                                    :checked="form.roles.includes(role)"
                                    @update:checked="toggleRole(role)"
                                    @click.stop
                                />
                                <Label :for="`role-${role}`" class="font-medium text-sm text-neutral-700 dark:text-neutral-300 cursor-pointer select-none">
                                    {{ role }}
                                </Label>
                            </div>
                        </div>
                        <p
                            v-if="form.errors.roles"
                            class="text-xs text-red-500 mt-1"
                        >
                            {{ form.errors.roles }}
                        </p>
                    </div>

                    <!-- Actions Form footer -->
                    <div
                        class="flex items-center gap-3 border-t border-neutral-200/40 dark:border-neutral-800/40 pt-6"
                    >
                        <Button type="submit" :disabled="form.processing" class="tahoe-button-primary">
                            Create User
                        </Button>
                        <Button as-child class="tahoe-button-secondary">
                            <Link href="/admin/users">Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
