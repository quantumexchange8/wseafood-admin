<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import dayjs from "dayjs";
import ManagePoint from "@/Pages/Point/ManagePoint.vue";

defineProps({
    pointLogsCount: Number,
    pointLogLastUpdatedAt: String,
})
</script>

<template>
    <AuthenticatedLayout :title="$t('public.point_history')" >
        <div class="flex flex-col items-center gap-4 self-stretch">
            <div class="flex items-center justify-center gap-4 self-stretch">
                <div class="flex flex-col items-start flex-1">
                    <div class="self-stretch dark:text-white text-2xl font-bold">
                        {{ $t('public.point_history') }}
                    </div>
                    <div class="self-stretch text-surface-500 text-sm">
                        {{ $t('public.last_update_on') }}: {{ dayjs(pointLogLastUpdatedAt).format('D MMM YYYY, h:mmA') }}
                    </div>
                </div>

                <ManagePoint />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
