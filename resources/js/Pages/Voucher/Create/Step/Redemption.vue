<script setup>
import {Button, Card, Divider, DatePicker, InputText, InputNumber, MultiSelect, Select, RadioButton} from "primevue";
import FormLabel from "@/Components/FormLabel.vue";
import IconAlertTooltip from "@/Components/IconAlertTooltip.vue";
import InputError from "@/Components/InputError.vue";
import {computed, watch} from "vue";

const props = defineProps({
    modelValue: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) },
})
const emit = defineEmits(["update:modelValue"]);

const redemption = computed({
    get: () => props.modelValue,
    set: (val) => emit("update:modelValue", val),
})

const claimMethods = [
    'point_to_claim',
    'code_to_claim',
    'add_for_member',
];

const requirementTypes = [
    'no_requirement',
    'min_spend'
];

const validTypes = [
    'all_day_time',
    'specific_day',
    'specific_time',
    'specific_day_time',
];

const weekdays = [
    {label: 'monday', value: 1},
    {label: 'tuesday', value: 2},
    {label: 'wednesday', value: 3},
    {label: 'thursday', value: 4},
    {label: 'friday', value: 5},
    {label: 'saturday', value: 6},
    {label: 'sunday', value: 0},
]

const defaultPointToClaim = { redeem_point: null }
const defaultCodeToClaim = { voucher_code: "" }
const defaultAddForMember = {
    activation_rule: "first_time_registration",
    event_type: "member_birthday",
    special_holiday_date: null,
    amount_paid: null,
}

watch(
    () => props.modelValue.claim_method,
    (val) => {
        if (!val) return

        emit("update:modelValue", {
            ...props.modelValue,
            claim_method: val,
            point_to_claim: { ...defaultPointToClaim },
            code_to_claim: { ...defaultCodeToClaim },
            add_for_member: { ...defaultAddForMember },
        })
    }
)

const eventRulePresets = {
    first_time_registration: {
        event_type: "member_birthday",
        special_holiday_date: null,
        amount_paid: null,
    },
    event_based: {
        event_type: "member_birthday",
        special_holiday_date: null,
        amount_paid: null,
    },
    special_holiday: {
        event_type: "special_holiday",
        special_holiday_date: null,
        amount_paid: null,
    },
    amount_paid: {
        event_type: "member_birthday",
        special_holiday_date: null,
        amount_paid: null,
    },
}

watch(
    () => props.modelValue.add_for_member.activation_rule,
    (val) => {
        if (!val) return

        emit("update:modelValue", {
            ...props.modelValue,
            add_for_member: {
                ...props.modelValue.add_for_member,
                activation_rule: val,
                ...eventRulePresets[val],
            },
        })
    }
)
</script>

<template>
    <div class="flex flex-col gap-4">
        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold flex gap-1 items-start">
                        {{ $t('public.claim_method') }} <span class="text-red-500 text-xs pt-1">*</span>
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
                        {{ $t('public.claim_method_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="flex flex-col gap-5 self-stretch p-5">
                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.method')"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="flex items-center gap-5 h-11">
                                <div v-for="method in claimMethods" class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="redemption.claim_method"
                                        :inputId="`method_${method}`"
                                        :value="method"
                                        :invalid="!!errors?.claim_method"
                                    />
                                    <div class="flex gap-1 items-center">
                                        <label :for="`method_${method}`" class="text-sm">
                                            {{ $t(`public.${method}`) }}
                                        </label>
                                        <IconAlertTooltip :message="$t(`public.${method}_caption`)" />
                                    </div>
                                </div>
                            </div>
                            <InputError :message="errors?.claim_method?.[0] ?? ''" />
                        </div>
                    </div>

                    <div
                        v-if="redemption.claim_method === 'point_to_claim'"
                        class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch"
                    >
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.point_to_redeem')"
                            for="redeem_point"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="relative w-full">
                                <InputNumber
                                    v-model="redemption.redeem_point"
                                    placeholder="100"
                                    inputId="redeem_point"
                                    fluid
                                    :invalid="!!errors?.redeem_point"
                                    inputClass="w-full pr-8 md:min-w-80"
                                />

                                <div class="absolute right-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                                    PTS
                                </div>
                            </div>
                            <InputError :message="errors?.redeem_point?.[0] ?? ''" />
                        </div>
                    </div>

                    <div
                        v-if="redemption.claim_method === 'code_to_claim'"
                        class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch"
                    >
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.voucher_code')"
                            for="voucher_code"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch w-full md:max-w-80">
                            <div class="flex gap-3 items-center w-full">
                                <InputText
                                    v-model="redemption.voucher_code"
                                    id="voucher_code"
                                    class="w-full"
                                    placeholder="eg. XXXXXX"
                                    :invalid="!!errors?.voucher_code"
                                />

                                <Button
                                    type="button"
                                    outlined
                                    :label="$t('public.generate')"
                                    class="w-40"
                                />
                            </div>
                            <InputError :message="errors?.voucher_code?.[0] ?? ''" />
                        </div>
                    </div>

                    <div
                        v-if="redemption.claim_method === 'add_for_member'"
                        class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch"
                    >
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.activation_rule')"
                            for="activation_rule"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <Select
                                v-model="redemption.add_for_member.activation_rule"
                                :options="['first_time_registration', 'event_based', 'amount_paid']"
                                :placeholder="$t('public.select_activation_rule')"
                                :invalid="!!errors?.activation_rule"
                                class="w-full md:min-w-80"
                            >
                                <template #value="{value, placeholder}">
                                    <div v-if="value" class="flex items-center">
                                        {{ $t(`public.${value}`) }}
                                    </div>
                                    <span v-else>{{ placeholder }}</span>
                                </template>

                                <template #option="{option}">
                                    <div>
                                        {{ $t(`public.${option}`) }}
                                    </div>
                                </template>
                            </Select>
                            <InputError :message="errors?.activation_rule?.[0] ?? ''" />
                        </div>
                    </div>

                    <div
                        v-if="redemption.add_for_member.activation_rule === 'event_based'"
                        class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch"
                    >
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.type_of_event')"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="flex items-center gap-5 h-11">
                                <div v-for="type in ['member_birthday', 'special_holiday']" class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="redemption.add_for_member.event_type"
                                        :inputId="`type_${type}`"
                                        :value="type"
                                        :invalid="!!errors?.event_type"
                                    />
                                    <div class="flex gap-1 items-center">
                                        <label :for="`type_${type}`" class="text-sm">
                                            {{ $t(`public.${type}`) }}
                                        </label>
                                        <IconAlertTooltip :message="$t(`public.${type}_caption`)" />
                                    </div>
                                </div>
                            </div>
                            <InputError :message="errors?.event_type?.[0] ?? ''" />
                        </div>
                    </div>

                    <div
                        v-if="redemption.add_for_member.event_type === 'special_holiday'"
                        class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch"
                    >
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.select_date')"
                            for="special_holiday_date"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <DatePicker
                                v-model="redemption.add_for_member.special_holiday_date"
                                :manualInput="false"
                                dateFormat="dd/mm/yy"
                                placeholder="dd/mm/yyyy"
                                class="w-full md:min-w-80"
                                :invalid="!!errors?.special_holiday_date"
                            />
                            <InputError :message="errors?.special_holiday_date?.[0] ?? ''" />
                        </div>
                    </div>

                    <div
                        v-if="redemption.add_for_member.activation_rule === 'amount_paid'"
                        class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch"
                    >
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.amount_paid')"
                            for="amount_paid"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="relative w-full">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                                    RM
                                </div>
                                <InputNumber
                                    v-model="redemption.add_for_member.amount_paid"
                                    placeholder="100"
                                    inputId="amount_paid"
                                    fluid
                                    :invalid="!!errors?.amount_paid"
                                    inputClass="w-full pl-10 md:min-w-80"
                                />
                            </div>
                            <InputError :message="errors?.amount_paid?.[0] ?? ''" />
                        </div>
                    </div>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold flex gap-1 items-start">
                        {{ $t('public.claim_limit') }} <span class="text-red-500 text-xs pt-1">*</span>
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
                        {{ $t('public.claim_limit_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="flex flex-col gap-5 self-stretch p-5">
                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel class="min-w-40 flex items-center gap-2">
                            {{ $t('public.claim_limit') }}
                            <IconAlertTooltip :message="$t('public.claim_limit_desc')" />
                        </FormLabel>

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="flex items-center gap-5 h-11">
                                <div class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="modelValue.claim_limit"
                                        inputId="limited"
                                        value="limited"
                                        :invalid="errors?.claim_limit"
                                    />
                                    <label for="limited" class="text-sm">
                                        {{ $t('public.limited') }}
                                    </label>
                                </div>
                                <div class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="modelValue.claim_limit"
                                        inputId="unlimited"
                                        value="unlimited"
                                        :invalid="errors?.claim_limit"
                                    />
                                    <label for="unlimited" class="text-sm">
                                        {{ $t('public.unlimited') }}
                                    </label>
                                </div>
                            </div>
                            <InputError :message="errors?.claim_limit?.[0] ?? ''" />
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel class="min-w-40 flex items-center gap-2" for="voucher_limit">
                            {{ $t('public.limit_per_voucher') }}
                            <IconAlertTooltip :message="$t('public.limit_per_voucher_caption')" />
                        </FormLabel>

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="relative w-full">
                                <InputNumber
                                    v-model="redemption.voucher_limit"
                                    placeholder="100"
                                    inputId="voucher_limit"
                                    fluid
                                    :invalid="!!errors?.voucher_limit"
                                    inputClass="w-full pr-8 md:min-w-80"
                                    :disabled="redemption.claim_limit === 'unlimited'"
                                />

                                <div class="absolute right-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                                    {{ $t('public.claims') }}
                                </div>
                            </div>
                            <InputError :message="errors?.voucher_limit?.[0] ?? ''" />
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel class="min-w-40 flex items-center gap-2" for="renew_voucher_limit">
                            {{ $t('public.renew_limit') }}
                            <IconAlertTooltip :message="$t('public.renew_limit_caption')" />
                        </FormLabel>

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <Select
                                v-model="redemption.renew_voucher_limit"
                                :options="['never_renew']"
                                :placeholder="$t('public.select_type')"
                                :invalid="!!errors?.renew_voucher_limit"
                                class="w-full md:min-w-80"
                                :disabled="redemption.claim_limit === 'unlimited'"
                            >
                                <template #value="{value, placeholder}">
                                    <div v-if="value" class="flex items-center">
                                        {{ $t(`public.${value}`) }}
                                    </div>
                                    <span v-else>{{ placeholder }}</span>
                                </template>

                                <template #option="{option}">
                                    <div>
                                        {{ $t(`public.${option}`) }}
                                    </div>
                                </template>
                            </Select>
                            <InputError :message="errors?.renew_voucher_limit?.[0] ?? ''" />
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel class="min-w-40 flex items-center gap-2" for="claim_amount_per_member">
                            {{ $t('public.limit_per_member') }}
                            <IconAlertTooltip :message="$t('public.limit_per_member_caption')" />
                        </FormLabel>

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="relative w-full">
                                <InputNumber
                                    v-model="redemption.claim_amount_per_member"
                                    placeholder="100"
                                    inputId="claim_amount_per_member"
                                    fluid
                                    :invalid="!!errors?.claim_amount_per_member"
                                    inputClass="w-full pr-8 md:min-w-80"
                                    :disabled="redemption.claim_limit === 'unlimited'"
                                />

                                <div class="absolute right-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                                    {{ $t('public.claims') }}
                                </div>
                            </div>
                            <InputError :message="errors?.claim_amount_per_member?.[0] ?? ''" />
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel class="min-w-40 flex items-center gap-2" for="renew_claim_limit">
                            {{ $t('public.renew_limit') }}
                            <IconAlertTooltip :message="$t('public.renew_limit_caption')" />
                        </FormLabel>

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <Select
                                v-model="redemption.renew_claim_limit"
                                :options="['never_renew']"
                                :placeholder="$t('public.select_type')"
                                :invalid="!!errors?.renew_claim_limit"
                                class="w-full md:min-w-80"
                                :disabled="redemption.claim_limit === 'unlimited'"
                            >
                                <template #value="{value, placeholder}">
                                    <div v-if="value" class="flex items-center">
                                        {{ $t(`public.${value}`) }}
                                    </div>
                                    <span v-else>{{ placeholder }}</span>
                                </template>

                                <template #option="{option}">
                                    <div>
                                        {{ $t(`public.${option}`) }}
                                    </div>
                                </template>
                            </Select>
                            <InputError :message="errors?.renew_claim_limit?.[0] ?? ''" />
                        </div>
                    </div>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold flex gap-1 items-start">
                        {{ $t('public.usage_criteria') }} <span class="text-red-500 text-xs pt-1">*</span>
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
                        {{ $t('public.usage_criteria_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="flex flex-col gap-5 self-stretch p-5">
                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel class="min-w-40 flex items-center gap-2" for="validity_count">
                            {{ $t('public.validity') }}
                            <IconAlertTooltip :message="$t('public.validity_caption')" />
                        </FormLabel>

                        <div class="flex flex-col items-start gap-1 self-stretch w-full md:max-w-80">
                            <div class="flex gap-3 items-center w-full">
                                <InputNumber
                                    v-model="modelValue.validity_count"
                                    placeholder="XX"
                                    inputId="validity_count"
                                    fluid
                                    :max="modelValue.discount_type === 'percentage' ? 100 : null"
                                    :invalid="!!errors?.validity_count"
                                    input-class="max-w-20"
                                />

                                <Select
                                    v-model="redemption.validity_count_type"
                                    :options="['day']"
                                    :placeholder="$t('public.select_type')"
                                    :invalid="!!errors?.validity_count_type"
                                    class="w-full"
                                >
                                    <template #value="{value, placeholder}">
                                        <div v-if="value" class="flex items-center">
                                            {{ $t(`public.valid_type_${value}`) }}
                                        </div>
                                        <span v-else>{{ placeholder }}</span>
                                    </template>

                                    <template #option="{option}">
                                        <div>
                                            {{ $t(`public.valid_type_${option}`) }}
                                        </div>
                                    </template>
                                </Select>
                            </div>
                            <InputError :message="errors?.validity_count?.[0] ?? ''" />
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.min_requirement')"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="flex items-center gap-5 h-11">
                                <div v-for="type in requirementTypes" class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="redemption.requirement_type"
                                        :inputId="`type_${type}`"
                                        :value="type"
                                        :invalid="!!errors?.requirement_type"
                                    />
                                    <label :for="`type_${type}`" class="text-sm">
                                        {{ $t(`public.${type}`) }}
                                    </label>
                                </div>
                                <InputError :message="errors?.requirement_type?.[0] ?? ''" />
                            </div>
                        </div>
                    </div>

                    <!-- Min Spend-->
                    <div
                        v-if="redemption.requirement_type === 'min_spend'"
                        class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch"
                    >
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.min_spend')"
                            for="min_spend_amount"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="relative w-full">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                                    RM
                                </div>
                                <InputNumber
                                    v-model="redemption.min_spend_amount"
                                    placeholder="100"
                                    inputId="min_spend_amount"
                                    fluid
                                    :min-fraction-digits="2"
                                    :max-fraction-digits="2"
                                    :invalid="!!errors?.min_spend_amount"
                                    inputClass="w-full pl-10 md:min-w-80"
                                />
                            </div>
                            <InputError :message="errors?.min_spend_amount?.[0] ?? ''" />
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel class="min-w-40 flex items-center gap-2">
                            {{ $t('public.valid_day_time') }}
                            <IconAlertTooltip :message="$t('public.valid_day_time_caption')" />
                        </FormLabel>

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="flex items-center gap-5 h-11">
                                <div v-for="type in validTypes" class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="redemption.valid_type"
                                        :inputId="`type_${type}`"
                                        :value="type"
                                        :invalid="!!errors?.valid_type"
                                    />
                                    <label :for="`type_${type}`" class="text-sm">
                                        {{ $t(`public.${type}`) }}
                                    </label>
                                </div>
                                <InputError :message="errors?.valid_type?.[0] ?? ''" />
                            </div>
                        </div>
                    </div>

                    <!-- Specific day and time -->
                    <div
                        v-if="redemption.valid_type === 'specific_day_time' || redemption.valid_type === 'specific_day'"
                        class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch"
                    >
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.valid_day')"
                            for="valid_days"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <MultiSelect
                                v-model="redemption.valid_days"
                                :options="weekdays"
                                optionLabel="label"
                                optionValue="value"
                                :placeholder="$t('public.select_day')"
                                :maxSelectedLabels="3"
                                class="w-full md:w-80"
                                :invalid="!!errors?.valid_days"
                            >
                                <template #value="{value, placeholder}">
                                    <div v-if="value && value.length">
                                        <span v-for="(val, i) in value" :key="i">
                                            {{ $t(`public.${weekdays.find(d => d.value === val)?.label}`) }}
                                            <span v-if="i < value.length - 1">, </span>
                                        </span>
                                    </div>
                                    <div v-else>
                                        {{ placeholder }}
                                    </div>
                                </template>
                                <template #option="{option}">
                                    {{ $t(`public.${option.label}`) }}
                                </template>
                            </MultiSelect>
                            <InputError :message="errors?.valid_days?.[0] ?? ''" />
                        </div>
                    </div>

                    <div
                        v-if="redemption.valid_type === 'specific_day_time' || redemption.valid_type === 'specific_time'"
                        class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch"
                    >
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.valid_time')"
                            for="valid_time"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="flex gap-3 items-center w-full md:max-w-80">
                                <DatePicker
                                    v-model="redemption.valid_time[0]"
                                    timeOnly
                                    fluid
                                    placeholder="hh:mm"
                                    class="w-full"
                                    :invalid="!!errors?.['valid_time.0']"
                                />
                                <Divider class="w-5" />
                                <DatePicker
                                    v-model="redemption.valid_time[1]"
                                    timeOnly
                                    fluid
                                    placeholder="hh:mm"
                                    class="w-full"
                                    :invalid="!!errors?.['valid_time.1']"
                                />
                            </div>
                            <InputError :message="errors?.['valid_time.1']?.[0] ?? ''" />
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel class="min-w-40 flex items-center gap-2">
                            {{ $t('public.stacking_rule') }}
                            <IconAlertTooltip :message="$t('public.stacking_rule_caption')" />
                        </FormLabel>

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="flex items-center gap-5 h-11">
                                <div class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="modelValue.can_stack"
                                        inputId="not_stackable"
                                        :value="false"
                                        :invalid="errors?.can_stack"
                                    />
                                    <label for="not_stackable" class="text-sm">
                                        {{ $t('public.not_stackable') }}
                                    </label>
                                </div>
                                <div class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="modelValue.can_stack"
                                        inputId="stackable"
                                        :value="true"
                                        :invalid="errors?.can_stack"
                                    />
                                    <label for="stackable" class="text-sm">
                                        {{ $t('public.stackable') }}
                                    </label>
                                </div>
                            </div>
                            <InputError :message="errors?.can_stack?.[0] ?? ''" />
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
