<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Circle,
    CreditCard,
    FolderGit2,
    LayoutGrid,
    Shield,
    type LucideIcon,
} from 'lucide-vue-next';
import * as LucideIcons from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import TeamSwitcher from '@/components/TeamSwitcher.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();

const dashboardUrl = computed(() => '/dashboard');

/**
 * Resolve a Lucide icon name string to the actual component.
 */
function resolveIcon(iconName: string): LucideIcon {
    const icons = LucideIcons as unknown as Record<string, LucideIcon>;
    return icons[iconName] ?? Circle;
}

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
    ];

    // Dynamic menus from database (exclude Pembayaran, it's a separate button)
    const navigation = page.props.navigation ?? [];
    for (const menu of navigation) {
        if (menu.url === '/payments') continue;
        items.push({
            title: menu.title,
            href: menu.url,
            icon: resolveIcon(menu.icon),
        });
    }

    // Admin link (always last, only for super-admin)
    if (page.props.auth?.isAdmin) {
        items.push({
            title: 'Admin',
            href: '/admin/users',
            icon: Shield,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <SidebarMenu>
                <SidebarMenuItem>
                    <TeamSwitcher />
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <!-- Prominent Payment Button -->
            <div class="px-3 pt-2 pb-1 group-data-[collapsible=icon]:px-1">
                <Link
                    href="/payments"
                    class="flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2.5 text-sm font-medium text-white transition-colors hover:bg-emerald-700 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-2"
                >
                    <CreditCard class="h-4 w-4 shrink-0" />
                    <span class="group-data-[collapsible=icon]:hidden">Pembayaran</span>
                </Link>
            </div>

            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
