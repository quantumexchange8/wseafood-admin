<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {generalFormat} from "@/Composables/format.js";
import UsageTable from "@/Pages/Voucher/UsageActivity/UsageTable.vue";
import {Button} from "primevue";
import {IconRosetteDiscountCheck} from "@tabler/icons-vue";

defineProps({
    lastUpdatedDate: String,
    usageCount: Number
});

const {formatDateTime} = generalFormat();
</script>

<template>
    <AuthenticatedLayout :title="$t('public.usage_activity')">
        <div class="flex flex-col items-center gap-4 self-stretch">
            <div class="flex items-center justify-center gap-4 self-stretch">
                <div class="flex flex-col items-start flex-1">
                    <div class="self-stretch text-2xl font-bold">
                        {{ $t('public.usage_activity') }}
                    </div>
                    <div class="self-stretch text-sm text-surface-500 dark:text-surface-400">
                        {{ $t('public.last_update_on') }}: {{ lastUpdatedDate ? formatDateTime(lastUpdatedDate) : '-' }}
                    </div>
                </div>

                <Button
                    type="button"
                    as="a"
                    :label="$t('public.voucher_usage')"
                    :href="route('voucher.voucher_usage')" target="_blank"
                >
                    <template #icon>
                        <IconRosetteDiscountCheck :size="20"/>
                    </template>
                </Button>
            </div>

            <UsageTable
                :usageCount="usageCount"
            />
        </div>
    </AuthenticatedLayout>
</template>
