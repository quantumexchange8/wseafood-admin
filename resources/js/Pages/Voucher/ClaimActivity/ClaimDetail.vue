<script setup>
import dayjs from "dayjs";
import {IconCircleCheck} from "@tabler/icons-vue";
import {Badge, Button, Image} from "primevue";
import {generalFormat} from "@/Composables/format.js";
import {computed} from "vue";
import {trans} from "laravel-vue-i18n";

const props = defineProps({
    redemption: Object
});

const {formatAmount} = generalFormat();

const weekdays = [
    {label: 'monday', value: 1},
    {label: 'tuesday', value: 2},
    {label: 'wednesday', value: 3},
    {label: 'thursday', value: 4},
    {label: 'friday', value: 5},
    {label: 'saturday', value: 6},
    {label: 'sunday', value: 0},
]

const formattedValidDays = computed(() => {
    const days = (props.redemption.voucher.validities || [])
        .flatMap(v => v.weekday || [])

    // Sort for consistency
    const sorted = [...days].sort((a, b) => a - b)

    const isWeekdays = sorted.length === 5 && [1,2,3,4,5].every(v => sorted.includes(v))
    const isWeekends = sorted.length === 2 && [6,0].every(v => sorted.includes(v))
    const isEveryday = sorted.length === 7 && [0,1,2,3,4,5,6].every(v => sorted.includes(v))

    if (isEveryday) return trans('public.everyday')
    if (isWeekdays) return trans('public.weekdays')
    if (isWeekends) return trans('public.weekends')

    // else map to day names
    return sorted
        .map(v => trans(`public.${weekdays.find(d => d.value === v)?.label}`))
        .join(', ')
})
</script>

<template>
    <div class="flex flex-col w-full">
        <div v-if="redemption.status === 'used'" class="bg-green-100 dark:bg-green-950 px-5 py-3 flex gap-2 items-center self-stretch w-full text-green-800 dark:text-green-200">
            <IconCircleCheck size="20" stroke-width="1.5" />
            <div class="font-bold text-sm">
                {{ $t('public.used_on', {date: dayjs(redemption.used_at).format('DD MMM YYYY')}) }}
            </div>
        </div>
        <div v-else-if="redemption.status === 'expired'" class="bg-yellow-100 dark:bg-yellow-950 px-5 py-3 flex gap-2 items-center self-stretch w-full text-yellow-800 dark:text-yellow-200">
            <IconCircleCheck size="20" stroke-width="1.5" />
            <div class="font-bold text-sm">
                {{ $t('public.expired_on', {date: dayjs(redemption.expired_at).format('DD MMM YYYY')}) }}
            </div>
        </div>
        <!-- Image -->
        <Image
            :src="redemption.voucher.media[0].original_url"
            class="bg-surface-200 dark:bg-surface-700"
            image-class="h-80 rounded-md object-contain w-full"
        />

        <!-- Step 1 -->
        <div class="flex flex-col gap-5 py-6 items-center self-stretch px-3">
            <div class="flex items-center gap-2 w-full">
                <Badge
                    :value="1"
                />
                <div class="text-surface-950 dark:text-white font-bold">{{ $t('public.general_voucher_detail') }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full">
                <div class="flex flex-col text-sm">
                    <div class="text-surface-500 dark:text-surface-400">
                        {{ $t('public.voucher_type') }}
                    </div>
                    <div class="font-bold text-surface-950 dark:text-white">
                        {{ $t(`public.${redemption.voucher.voucher_type}`) }}
                    </div>
                </div>
                <div class="flex flex-col text-sm">
                    <div class="text-surface-500 dark:text-surface-400">
                        {{ $t('public.voucher_name') }}
                    </div>
                    <div class="font-bold text-surface-950 dark:text-white">
                        {{ redemption.voucher.voucher_name ?? '-' }}
                    </div>
                </div>
                <div class="flex flex-col text-sm">
                    <div class="text-surface-500 dark:text-surface-400">
                        {{ $t('public.description') }}
                    </div>
                    <div class="font-bold text-surface-950 dark:text-white">
                        {{ redemption.voucher.description ?? '-' }}
                    </div>
                </div>
                <div class="flex flex-col text-sm">
                    <div class="text-surface-500 dark:text-surface-400">
                        {{ $t('public.campaign_period') }}
                    </div>
                    <div class="font-bold text-surface-950 dark:text-white">
                        <div v-if="redemption.voucher.campaign_period">
                            {{ dayjs(redemption.voucher.validities[0].start_date).format('DD/MM/YYYY') }} - {{ dayjs(redemption.voucher.validities[0].end_date).format('DD/MM/YYYY') }}
                        </div>
                        <div v-else>
                            {{ $t('public.no_campaign_period') }}
                        </div>
                    </div>
                </div>
                <div class="flex flex-col text-sm">
                    <div class="text-surface-500 dark:text-surface-400">
                        {{ $t('public.eligible_service') }}
                    </div>
                    <div class="font-bold text-surface-950 dark:text-white">
                        {{ $t(`public.${redemption.voucher.eligible_service}`) }}
                    </div>
                </div>
                <div class="flex flex-col text-sm">
                    <div class="text-surface-500 dark:text-surface-400">
                        {{ $t('public.discount_rate') }}
                    </div>
                    <div class="font-bold text-surface-950 dark:text-white">
                        <div v-if="redemption.voucher.discount_type === 'percentage'">
                            {{ $t('public.discount_rate_capped', {percent: redemption.voucher?.discount_rate, amount: redemption.voucher?.capped_amount}) }}
                        </div>
                        <div v-else>
                            {{ formatAmount(redemption.voucher.discount_rate, 2, 'RM ') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2-->
        <div class="flex flex-col gap-5 py-6 items-center self-stretch px-3">
            <div class="flex items-center gap-2 w-full">
                <Badge
                    :value="2"
                />
                <div class="text-surface-950 dark:text-white font-bold">{{ $t('public.redemption_usage_condition') }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full">
                <div class="flex flex-col text-sm">
                    <div class="text-surface-500 dark:text-surface-400">
                        {{ $t('public.claim_method') }}
                    </div>
                    <div class="font-bold text-surface-950 dark:text-white">
                        {{ $t(`public.${redemption.voucher.claim_method}`) }}
                    </div>
                </div>

                <div class="flex flex-col text-sm">
                    <div class="text-surface-500 dark:text-surface-400">
                        <span v-if="redemption.voucher.claim_method === 'point_to_claim'">{{ $t('public.point_to_redeem') }}</span>
                        <span v-else-if="redemption.voucher.claim_method === 'code_to_claim'">{{ $t('public.voucher_code') }}</span>
                        <span v-else-if="redemption.voucher.claim_method === 'add_for_member'">{{ $t('public.activation_rule') }}</span>
                    </div>
                    <div class="font-bold text-surface-950 dark:text-white">
                        <!-- Point -->
                        <div v-if="redemption.voucher.claim_method === 'point_to_claim'">
                            {{ redemption.voucher.redeem_point ?? '-' }}
                        </div>

                        <!-- Code -->
                        <div v-else-if="redemption.voucher.claim_method === 'code_to_claim'">
                            {{ redemption.voucher.voucher_code ?? '-' }}
                        </div>

                        <!-- Auto add -->
                        <div v-else-if="redemption.voucher.claim_method === 'add_for_member'">
                            <div v-if="redemption.voucher.member_rule.activation_rule === 'first_time_registration'">
                                {{ $t('public.first_time_registration') }}
                            </div>
                            <div v-else-if="redemption.voucher.member_rule.activation_rule === 'event_based'">
                                <span v-if="redemption.voucher.member_rule.event_type === 'member_birthday'">{{ $t(`public.${redemption.voucher.member_rule?.event_type}`) }}</span>
                                <span v-else>{{ $t(`public.${redemption.voucher.member_rule?.event_type}`) }} - {{ dayjs(redemption.voucher.member_rule?.special_holiday_date).format('DD/MM/YYYY') }}</span>
                            </div>
                            <div v-else-if="redemption.voucher.member_rule.activation_rule === 'amount_paid'">
                                {{ $t('public.spend') }} RM {{ formatAmount(redemption.voucher.member_rule.amount_paid) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col text-sm">
                    <div class="text-surface-500 dark:text-surface-400">
                        {{ $t('public.limit_per_voucher') }}
                    </div>
                    <div class="font-bold text-surface-950 dark:text-white">
                        <div v-if="redemption.voucher.claim_limit">
                            {{ formatAmount(redemption.voucher.voucher_limit, 0, '') }} ({{ $t(`public.${redemption.voucher.renew_voucher_limit}`) }})
                        </div>
                        <div v-else>
                            {{ $t('public.unlimited') }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col text-sm">
                    <div class="text-surface-500 dark:text-surface-400">
                        {{ $t('public.limit_per_member') }}
                    </div>
                    <div class="font-bold text-surface-950 dark:text-white">
                        <div v-if="redemption.voucher.claim_limit">
                            {{ formatAmount(redemption.voucher.claim_amount_per_member, 0, '') }} ({{ $t(`public.${redemption.voucher.renew_claim_limit}`) }})
                        </div>
                        <div v-else>
                            {{ $t('public.unlimited') }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col text-sm">
                    <div class="text-surface-500 dark:text-surface-400">
                        {{ $t('public.validity') }}
                    </div>
                    <div class="font-bold text-surface-950 dark:text-white">
                        {{ formatAmount(redemption.voucher.validity_count, 0, '') }} {{ $t(`public.valid_type_${redemption.voucher.validity_count_type}`) }}
                    </div>
                </div>

                <div class="flex flex-col text-sm">
                    <div class="text-surface-500 dark:text-surface-400">
                        {{ $t('public.min_requirement') }}
                    </div>
                    <div class="font-bold text-surface-950 dark:text-white">
                        <div v-if="redemption.voucher.requirement_type === 'no_requirement'">
                            {{ $t('public.no_requirement') }}
                        </div>
                        <div v-else>
                            {{ $t('public.min_spend') }} {{ formatAmount(redemption.voucher.min_spend_amount, 2, 'RM ') }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col text-sm">
                    <div class="text-surface-500 dark:text-surface-400">
                        {{ $t('public.valid_day_time') }}
                    </div>
                    <div class="font-bold text-surface-950 dark:text-white flex gap-1">
                        {{ $t(`public.${redemption.voucher.valid_type}`) }}
                        <span>-</span>
                        <div v-if="redemption.voucher.valid_type === 'specific_day'" class="text-surface-500 dark:text-surface-400 font-normal">
                            {{ formattedValidDays }}
                        </div>
                        <div v-else-if="redemption.voucher.valid_type === 'specific_time'" class="text-surface-500 dark:text-surface-400 font-normal">
                            {{ redemption.voucher.validities[0].start_time }} - {{ redemption.voucher.validities[0].end_time }}
                        </div>
                        <div v-else class="text-surface-500 dark:text-surface-400 font-normal">
                            {{ formattedValidDays }} |
                            {{ redemption.voucher.validities[0].start_time }} - {{ redemption.voucher.validities[0].end_time }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col text-sm">
                    <div class="text-surface-500 dark:text-surface-400">
                        {{ $t('public.stacking_rule') }}
                    </div>
                    <div class="font-bold text-surface-950 dark:text-white">
                        {{ redemption.voucher.can_stack ? $t('public.stackable') : $t('public.not_stackable') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="flex flex-col gap-5 py-6 items-center self-stretch px-3">
            <div class="flex items-center gap-2 w-full">
                <Badge
                    :value="3"
                />
                <div class="text-surface-950 dark:text-white font-bold">{{ $t('public.other_voucher_setting') }}</div>
            </div>

            <div class="flex flex-col gap-2 w-full text-sm">
                <div class="text-surface-500 dark:text-surface-400">
                    {{ $t('public.voucher_highlight') }}
                </div>
                <div class="prose dark:prose-invert" v-html="redemption.voucher.voucher_highlight"></div>
            </div>
        </div>
    </div>
</template>

