<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DashboardOverview from "@/Pages/Dashboard/DashboardOverview.vue";
import MemberAnalysis from "@/Pages/Dashboard/MemberAnalysis/MemberAnalysis.vue";

defineProps({
    totalUsers: Number,
    usersTrend: Number,
    totalProducts: Number,
    productsTrend: Number,
    pointsGiven: Number,
    pointsGivenTrend: Number,
})
</script>

<template>
    <AuthenticatedLayout :title="$t('public.dashboard')">
        <div class="flex flex-col gap-5 items-center self-stretch w-full">
            <div class="flex flex-col items-start self-stretch w-full">
                <div class="self-stretch text-2xl font-bold dark:text-white">
                    {{ $t('public.welcome_back') }}
                </div>
                <div class="self-stretch text-sm text-surface-500">
                    {{ $t('public.welcome_greet') }}
                </div>
            </div>

            <DashboardOverview
                :totalUsers="totalUsers"
                :usersTrend="usersTrend"
                :totalProducts="totalProducts"
                :productsTrend="productsTrend"
                :pointsGiven="pointsGiven"
                :pointsGivenTrend="pointsGivenTrend"
            />

            <MemberAnalysis />
        </div>
    </AuthenticatedLayout>
</template>
