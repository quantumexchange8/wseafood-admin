<script setup>
import SidebarLink from '@/Components/Sidebar/SidebarLink.vue'
import SidebarCollapsible from '@/Components/Sidebar/SidebarCollapsible.vue'
import SidebarCollapsibleItem from '@/Components/Sidebar/SidebarCollapsibleItem.vue'
import { sidebarState } from '@/Composables'
import {onMounted, ref, watchEffect} from "vue";
import {usePage} from "@inertiajs/vue3";
import {
    IconLayoutDashboard,
    IconUsers,
    IconUsersGroup,
    IconTemplate,
    IconCategory,
    IconCoinMonero,
    IconHistory,
    IconClockDollar,
    IconAdjustmentsDollar,
    IconTag,
    IconClipboardData,
    IconPhotoCog,
} from '@tabler/icons-vue';
import SidebarCategoryLabel from "@/Components/Sidebar/SidebarCategoryLabel.vue";
//
// const page = usePage();
// const pendingDepositCounts = ref(page.props.getPendingDepositCount);
// const pendingWithdrawalCounts = ref(page.props.pendingWithdrawalCounts);
//
// const getPendingCounts = async () => {
//     try {
//         const response = await axios.get(route('dashboard.getPendingCounts'));
//         pendingDepositCounts.value = response.data.pendingDepositCounts;
//         pendingWithdrawalCounts.value = response.data.pendingWithdrawalCounts;
//     } catch (error) {
//         console.error('Error pending counts:', error);
//     }
// };
//
// watchEffect(() => {
//     if (usePage().props.toast !== null) {
//         getPendingCounts();
//     }
// });
</script>

<template>
    <nav
        class="relative flex flex-col flex-1 max-h-full gap-1 items-center"
        :class="{
            'p-3': sidebarState.isOpen || sidebarState.isHovered,
            'px-5 py-3': !sidebarState.isOpen && !sidebarState.isHovered,
        }"
    >
        <!-- Dashboard -->
        <SidebarLink
            :title="'Dashboard'"
            :href="route('dashboard')"
            :active="route().current('dashboard')"
        >
            <template #icon>
                <IconLayoutDashboard :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <SidebarCategoryLabel
            :title="'Product'"
        />
        <SidebarLink
            :title="'Create Product'"
            :href="route('dashboard')"
            :active="route().current('profile.edit')"
        >
            <template #icon>
                <IconLayoutDashboard :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <SidebarLink
            :title="'Product List'"
            :href="route('dashboard')"
            :active="route().current('profile.edit')"
        >
            <template #icon>
                <IconLayoutDashboard :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>
    </nav>
</template>
