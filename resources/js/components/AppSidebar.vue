<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BriefcaseBusiness,
    LayoutGrid,
    UsersRound,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
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
import opportunities from '@/routes/opportunities';
import users from '@/routes/users';
import type { NavItem } from '@/types';

const page = usePage();

const currentTeamSlug = computed(() => {
    if (page.props.currentTeam?.slug) {
        return page.props.currentTeam.slug;
    }

    const [, teamSlugFromUrl] = page.url.split('/');

    return teamSlugFromUrl || null;
});

const dashboardUrl = computed(() =>
    currentTeamSlug.value ? dashboard(currentTeamSlug.value).url : '/',
);

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboardUrl.value,
        icon: LayoutGrid,
    },
    {
        title: 'Oportunidades',
        href: opportunities.index(),
        icon: BriefcaseBusiness,
    },
    {
        title: 'Usuários',
        href: users.index(),
        icon: UsersRound,
    },
]);

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardUrl">
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
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
