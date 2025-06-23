<script setup>
import SidebarLink from '@/Components/Sidebar/SidebarLink.vue'
import SidebarCollapsible from '@/Components/Sidebar/SidebarCollapsible.vue'
import SidebarCollapsibleItem from '@/Components/Sidebar/SidebarCollapsibleItem.vue'
import { sidebarState } from '@/Composables'
import {
    IconLayoutDashboard,
    IconCategory,
    IconHierarchy,
    IconBowlChopsticks,
    IconUsers,
    IconSpeakerphone,
    IconBellPlus,
} from '@tabler/icons-vue';
import SidebarCategoryLabel from "@/Components/Sidebar/SidebarCategoryLabel.vue";

</script>

<template>
    <nav
        class="relative flex flex-col flex-1 max-h-full gap-1 items-center"
        :class="{
            'p-3': sidebarState.isOpen || sidebarState.isHovered,
            'px-5 py-3': !sidebarState.isOpen && !sidebarState.isHovered,
        }"
    >
        <SidebarCategoryLabel
            :title="$t('public.main_menu')"
        />

        <!-- Dashboard -->
        <SidebarLink
            :title="$t('public.dashboard')"
            :href="route('dashboard')"
            :active="route().current('dashboard')"
        >
            <template #icon>
                <IconLayoutDashboard :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <SidebarCategoryLabel
            :title="$t('public.item_management')"
        />

        <SidebarCollapsible
            :title="$t('public.category')"
            :active="route().current('category.*')"
        >
            <template #icon>
                <IconCategory size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem
                :title="$t('public.category_list')"
                :href="route('category.index')"
                :active="route().current('category.index')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.create_category')"
                :href="route('category.create')"
                :active="route().current('category.create')"
            />
        </SidebarCollapsible>

        <SidebarCollapsible
            :title="$t('public.modifier_group')"
            :active="route().current('modifier.*')"
        >
            <template #icon>
                <IconHierarchy size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem
                :title="$t('public.modifier_group_list')"
                :href="route('modifier.group.index')"
                :active="route().current('modifier.group.index')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.create_modifier_group')"
                :href="route('modifier.group.create')"
                :active="route().current('modifier.group.create')"
            />
        </SidebarCollapsible>

        <SidebarCollapsible
            :title="$t('public.product')"
            :active="route().current('product.*')"
        >
            <template #icon>
                <IconBowlChopsticks size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem
                :title="$t('public.product_list')"
                :href="route('product.index')"
                :active="route().current('product.index')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.create_product')"
                :href="route('product.create')"
                :active="route().current('product.create')"
            />
        </SidebarCollapsible>

        <SidebarCategoryLabel
            :title="$t('public.user_management')"
        />

        <SidebarLink
            :title="$t('public.member')"
            :href="route('member.index')"
            :active="route().current('member.index')"
        >
            <template #icon>
                <IconUsers :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <SidebarCollapsible
            :title="$t('public.highlight')"
            :active="route().current('highlight.*')"
        >
            <template #icon>
                <IconSpeakerphone size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem
                :title="$t('public.highlight_list')"
                :href="route('highlight.index')"
                :active="route().current('highlight.index')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.create_highlight')"
                :href="route('highlight.create')"
                :active="route().current('highlight.create')"
            />
        </SidebarCollapsible>

        <SidebarCollapsible
            :title="$t('public.push_notification')"
            :active="route().current('notification.*')"
        >
            <template #icon>
                <IconBellPlus size="20" stroke-width="1.5" />
            </template>
            <SidebarCollapsibleItem
                :title="$t('public.push_notification_list')"
                :href="route('notification.index')"
                :active="route().current('notification.index')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.create_notification')"
                :href="route('notification.create')"
                :active="route().current('notification.create')"
            />
        </SidebarCollapsible>
    </nav>
</template>
