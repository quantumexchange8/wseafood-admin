<script setup>
import {Card, Button} from "primevue";
import {generalFormat} from "@/Composables/format.js";
import {IconArrowRight, IconTrendingUp, IconTrendingDown} from "@tabler/icons-vue";

defineProps({
    totalUsers: Number,
    usersTrend: Number,
    totalProducts: Number,
    productsTrend: Number,
    pointsGiven: Number,
    pointsGivenTrend: Number,
})

const {formatAmount} = generalFormat();
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full">
        <Card class="w-full">
            <template #content>
                <div class="p-4 flex flex-col gap-3 self-stretch">
                    <span class="font-medium">{{ $t('public.member_signups') }}</span>

                    <div class="flex flex-col">
                        <div class="text-xl font-bold">
                            {{ formatAmount(totalUsers, 0, '') }}
                        </div>
                        <div
                            v-if="usersTrend !== null"
                            :class="[
                                    'flex gap-1 items-center',
                                    {
                                        'text-green-500': usersTrend > 0,
                                        'text-red-500': usersTrend < 0,
                                    }
                                ]">
                            <IconTrendingUp
                                v-if="usersTrend > 0"
                                size="18"
                                stroke-width="1.5"
                            />
                            <IconTrendingDown
                                v-else-if="usersTrend < 0"
                                size="18"
                                stroke-width="1.5"
                            />
                            {{ formatAmount(usersTrend, 0, '') }} <span class="text-sm text-surface-500">{{ $t('public.vs_last_month') }}</span>
                        </div>
                    </div>

                    <Button
                        type="button"
                        severity="secondary"
                        outlined
                        size="small"
                        as="a"
                        :href="route('member.index')"
                    >
                        {{ $t('public.view_detail') }}
                        <IconArrowRight size="16" stroke-width="1.5" />
                    </Button>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #content>
                <div class="p-4 flex flex-col gap-3 self-stretch">
                    <span class="font-medium">{{ $t('public.total_products') }}</span>

                    <div class="flex flex-col">
                        <div class="text-xl font-bold">
                            {{ formatAmount(totalProducts, 0, '') }}
                        </div>
                        <div
                            v-if="productsTrend !== null"
                            :class="[
                                    'flex gap-1 items-center',
                                    {
                                        'text-green-500': productsTrend > 0,
                                        'text-red-500': productsTrend < 0,
                                    }
                                ]">
                            <IconTrendingUp
                                v-if="productsTrend > 0"
                                size="18"
                                stroke-width="1.5"
                            />
                            <IconTrendingDown
                                v-else-if="productsTrend < 0"
                                size="18"
                                stroke-width="1.5"
                            />
                            {{ formatAmount(productsTrend, 0, '') }} <span class="text-sm text-surface-500">{{ $t('public.vs_last_month') }}</span>
                        </div>
                    </div>

                    <Button
                        type="button"
                        severity="secondary"
                        outlined
                        size="small"
                        as="a"
                        :href="route('product.index')"
                    >
                        {{ $t('public.view_detail') }}
                        <IconArrowRight size="16" stroke-width="1.5" />
                    </Button>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #content>
                <div class="p-4 flex flex-col gap-3 self-stretch">
                    <span class="font-medium">{{ $t('public.points_given') }}</span>

                    <div class="flex flex-col">
                        <div class="text-xl font-bold">
                            {{ formatAmount(pointsGiven, 0, '') }}
                        </div>
                        <div
                            v-if="pointsGivenTrend !== null"
                            :class="[
                                    'flex gap-1 items-center',
                                    {
                                        'text-green-500': pointsGivenTrend > 0,
                                        'text-red-500': pointsGivenTrend < 0,
                                    }
                                ]">
                            <IconTrendingUp
                                v-if="pointsGivenTrend > 0"
                                size="18"
                                stroke-width="1.5"
                            />
                            <IconTrendingDown
                                v-else-if="pointsGivenTrend < 0"
                                size="18"
                                stroke-width="1.5"
                            />
                            {{ formatAmount(pointsGivenTrend, 0, '') }} <span class="text-sm text-surface-500">{{ $t('public.vs_last_month') }}</span>
                        </div>
                    </div>

                    <Button
                        type="button"
                        severity="secondary"
                        outlined
                        size="small"
                        as="a"
                        :href="route('point.index')"
                    >
                        {{ $t('public.view_detail') }}
                        <IconArrowRight size="16" stroke-width="1.5" />
                    </Button>
                </div>
            </template>
        </Card>
    </div>
</template>
