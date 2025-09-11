<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {generalFormat} from "@/Composables/format.js";
import ClaimTable from "@/Pages/Voucher/ClaimActivity/ClaimTable.vue";

defineProps({
    lastUpdatedDate: String,
    redemptionsCount: Number
});

const {formatDateTime} = generalFormat();
</script>

<template>
    <AuthenticatedLayout :title="$t('public.claim_activity')">
        <div class="flex flex-col items-center gap-4 self-stretch">
            <div class="flex items-center justify-center gap-4 self-stretch">
                <div class="flex flex-col items-start flex-1">
                    <div class="self-stretch text-2xl font-bold">
                        {{ $t('public.claim_activity') }}
                    </div>
                    <div class="self-stretch text-sm text-surface-500 dark:text-surface-400">
                        {{ $t('public.last_update_on') }}: {{ lastUpdatedDate ? formatDateTime(lastUpdatedDate) : '-' }}
                    </div>
                </div>
            </div>

            <ClaimTable
                :redemptionsCount="redemptionsCount"
            />
        </div>
    </AuthenticatedLayout>
</template>
