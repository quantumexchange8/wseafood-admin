<script setup>
import { ProgressSpinner, Skeleton } from 'primevue';
import { computed } from 'vue';

const props = defineProps({
    itemCount: Number,
})

const count = computed(() => {
    return props.itemCount < 2 ? 2 : props.itemCount;
})
</script>

<template>
    <div class="relative w-full">
        <div class="flex flex-col items-start self-stretch">
            <div
                v-for="i in count"
                :key="i"
                class="w-full py-4 px-5 flex items-center gap-4 border-b border-solid"
            >
                <div class='flex flex-col items-start gap-1'>
                    <Skeleton width='4rem' height="1.5rem" />
                </div>
            </div>
        </div>
        <div class="absolute inset-0 top-0 bg-slate-500 bg-opacity-10 flex items-center justify-center z-10">
            <div class="flex flex-col justify-center items-center gap-5 self-stretch">
                <ProgressSpinner
                    strokeWidth="4"
                    class="w-8 h-8"
                />
                <span class="text-sm font-semibold text-surface-700 dark:text-surface-300">
                    {{ $t('public.loading_data') }}
                </span>
            </div>
        </div>
    </div>
</template>