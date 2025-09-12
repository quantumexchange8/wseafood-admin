<script setup>
import { ProgressSpinner, Card, Skeleton } from 'primevue';
import { computed } from 'vue';
import StatusSwitch from "@/Components/StatusSwitch.vue";

</script>

<template>
    <div class="relative w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 w-full">
            <div
                v-for="redemption in 3"
                class="flex flex-col rounded-2xl border shadow-input dark:border-surface-700"
            >
                <!-- Image -->
                <div class="rounded-tl-xl rounded-tr-xl bg-surface-200 dark:bg-surface-700 h-24 w-full"></div>
                <div class="flex flex-col gap-1 text-sm p-3">
                    <div class="font-bold text-surface-950 dark:text-white">
                        {{ $t('public.no_voucher_found') }}
                    </div>
                    <div class="px-2 py-1 flex gap-2 items-center rounded-lg bg-surface-200 dark:bg-surface-800 text-xs w-fit font-medium text-surface-500 dark:text-surface-300">
                        {{ $t('public.expired_on', {date: 'DD/MM/YYYY'}) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute inset-0 top-0 bg-slate-500 bg-opacity-10 flex items-center justify-center z-10">
            <div class="flex flex-col justify-center items-center gap-5 self-stretch">
                <ProgressSpinner
                    strokeWidth="4"
                    class="w-16 h-16"
                />
                <span class="text-sm font-semibold text-surface-700 dark:text-surface-300">
                    {{ $t('public.loading_data') }}
                </span>
            </div>
        </div>
    </div>
</template>
