<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Button} from "primevue";
import {IconPlus} from "@tabler/icons-vue";
import {generalFormat} from "@/Composables/format.js";
import VoucherTable from "@/Pages/Voucher/Listing/VoucherTable.vue";

defineProps({
    lastUpdatedDate: String,
    vouchersCount: Number
});

const {formatDateTime} = generalFormat();
</script>

<template>
    <AuthenticatedLayout :title="$t('public.voucher_listing')">
        <div class="flex flex-col items-center gap-4 self-stretch">
            <div class="flex items-center justify-center gap-4 self-stretch">
                <div class="flex flex-col items-start flex-1">
                    <div class="self-stretch text-2xl font-bold">
                        {{ $t('public.voucher_listing') }}
                    </div>
                    <div class="self-stretch text-sm text-surface-500 dark:text-surface-400">
                        {{ $t('public.last_update_on') }}: {{ lastUpdatedDate ? formatDateTime(lastUpdatedDate) : '-' }}
                    </div>
                </div>
                <Button
                    type="button"
                    :label="$t('public.create_category')"
                    @click="() => $inertia.visit(route('voucher.create'))"
                >
                    <template #icon>
                        <IconPlus :size="20"/>
                    </template>
                </Button>
            </div>

            <VoucherTable
                :vouchersCount="vouchersCount"
            />
        </div>
    </AuthenticatedLayout>
</template>
