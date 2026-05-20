<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { KeyRound, Menu, Shield, Users, Sliders } from 'lucide-vue-next';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

interface AdminNavItem extends NavItem {
    icon: typeof Users;
}

const sidebarNavItems: AdminNavItem[] = [
    {
        title: 'Users',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'Roles',
        href: '/admin/roles',
        icon: Shield,
    },
    {
        title: 'Permissions',
        href: '/admin/permissions',
        icon: KeyRound,
    },
    {
        title: 'Menus',
        href: '/admin/menus',
        icon: Menu,
    },
    {
        title: 'App Settings',
        href: '/admin/settings',
        icon: Sliders,
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="flex min-h-[calc(100vh-4rem)] bg-muted/30">
        <!-- Admin Sidebar -->
        <aside
            class="hidden w-56 shrink-0 border-r bg-background p-4 lg:block"
        >
            <div class="mb-6">
                <h2 class="text-lg font-semibold tracking-tight">Admin</h2>
                <p class="text-xs text-muted-foreground">
                    Manage your application
                </p>
            </div>

            <nav class="space-y-1" aria-label="Admin navigation">
                <Link
                    v-for="item in sidebarNavItems"
                    :key="item.title"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                        isCurrentOrParentUrl(item.href)
                            ? 'bg-primary text-primary-foreground'
                            : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                    ]"
                >
                    <component :is="item.icon" class="h-4 w-4" />
                    {{ item.title }}
                </Link>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <slot />
        </main>
    </div>
</template>
