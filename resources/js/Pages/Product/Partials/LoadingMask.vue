<script setup>
import { ProgressSpinner, Card, Skeleton } from 'primevue';
import { computed } from 'vue';
import StatusSwitch from "@/Components/StatusSwitch.vue";

const props = defineProps({
    layout: String,
    productCount: Number,
})

const count = computed(() => {
    return props.productCount > 12 ? 12 : props.productCount;
})
</script>

<template>
    <div class="relative w-full py-5">
        <div
            v-if="layout === 'grid'"
            class="grid grid-cols-3 xl:grid-cols-4 items-stretch content-start gap-4 shrink-0 self-stretch"
        >
            <Card
                v-for="i in count"
                :key="i"
                class="min-w-[240px] flex flex-col items-start self-stretch"
            >
                <template #header>
                    <div class="w-full p-4 flex justify-center items-center">
                        <Skeleton width="30rem" height="15rem" border-radius="8px"></Skeleton>
                    </div>
                </template>
                <template #content>
                    <div class="px-4 pb-4 flex flex-col gap-3">
                        <div
                            class="font-bold text-surface-950 group-hover:text-primary transition"
                        >
                            <Skeleton width="4rem"></Skeleton>
                        </div>
                        <div class="flex justify-between items-end self-stretch">
                            <Skeleton width="3rem" height="1.5rem" class="my-1"></Skeleton>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
        <div
            v-else
            class="grid grid-cols-2 xl:grid-cols-3 items-stretch content-start gap-4 shrink-0 self-stretch"
        >
            <Card
                v-for="i in count"
                :key="i"
                class="min-w-[280px] p-4 flex flex-row items-start gap-4"
            >
                <template #header>
                    <div class="w-full flex justify-center items-center">
                        <Skeleton width="7rem" height="7rem" border-radius="8px"></Skeleton>
                    </div>
                </template>
                <template #content>
                    <div class="h-full flex flex-col justify-between items-start self-stretch">
                        <div class="flex flex-col items-start gap-2 self-stretch">
                            <Skeleton width="7rem" height="1.5rem"></Skeleton>
                            <Skeleton width="4rem"></Skeleton>
                        </div>
                        <Skeleton width="3rem" height="2rem"></Skeleton>
                    </div>
                </template>
            </Card>
        </div>
        <div class="absolute inset-0 top-5 bg-slate-500 bg-opacity-10 flex items-center justify-center z-10">
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
