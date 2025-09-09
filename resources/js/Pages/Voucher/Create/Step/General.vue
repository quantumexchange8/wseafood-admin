<script setup>
import {Card, DatePicker, InputText, InputNumber, Select, RadioButton} from "primevue";
import FormLabel from "@/Components/FormLabel.vue";
import {IconCurrencyDollar, IconPercentage} from "@tabler/icons-vue";
import IconAlertTooltip from "@/Components/IconAlertTooltip.vue";
import InputError from "@/Components/InputError.vue";
import {watch} from "vue";
import OptionCard from "@/Components/OptionCard.vue";

const props = defineProps({
    modelValue: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) },
})
const emit = defineEmits(["update:modelValue"])

watch(
    () => props.modelValue.discount_type,
    (val) => {
        emit("update:modelValue", {
            ...props.modelValue,
            discount_limit: val !== "amount",
        })
    }
)
</script>

<template>
    <div class="flex flex-col gap-4">
        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.type') }}
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
                        {{ $t('public.voucher_type_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch p-5">
                    <FormLabel
                        class="min-w-40"
                        :label="$t('public.voucher_type')"
                        required
                        for="voucher_type"
                    />

                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <Select
                            v-model="modelValue.voucher_type"
                            input-id="voucher_type"
                            :options="['value_discount']"
                            :placeholder="$t('public.select_type')"
                            class="w-full md:min-w-80"
                            :invalid="!!errors?.voucher_type"
                        >
                            <template #value="{value, placeholder}">
                                <div v-if="value">
                                    {{ $t(`public.${value}`) }}
                                </div>
                                <span v-else>{{ placeholder }}</span>
                            </template>
                            <template #option="{option}">
                                {{ $t(`public.${option}`) }}
                            </template>
                        </Select>
                        <InputError :message="errors?.voucher_type?.[0]" />
                    </div>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold flex gap-1 items-start">
                        {{ $t('public.general_detail') }} <span class="text-red-500 text-xs pt-1">*</span>
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
                        {{ $t('public.general_detail_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="flex flex-col gap-5 self-stretch p-5">
                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.voucher_name')"
                            for="voucher_name"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <InputText
                                v-model="modelValue.voucher_name"
                                id="voucher_name"
                                class="w-full md:min-w-80"
                                placeholder="eg. RMXX OFF"
                                :invalid="!!errors?.voucher_name"
                            />
                            <InputError :message="errors?.voucher_name?.[0] ?? ''" />
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.description')"
                            for="description"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <InputText
                                v-model="modelValue.description"
                                id="description"
                                class="w-full md:min-w-80"
                                :placeholder="$t('public.enter_content')"
                                :invalid="!!errors?.description"
                            />
                            <InputError :message="errors?.description?.[0] ?? ''" />
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel class="min-w-40 flex items-center gap-2">
                            {{ $t('public.campaign_period') }}
                            <IconAlertTooltip :message="$t('public.campaign_period_caption')" />
                        </FormLabel>

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="flex items-center gap-5 h-11">
                                <div class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="modelValue.campaign_period"
                                        inputId="period_yes"
                                        :value="true"
                                        :invalid="errors?.campaign_period"
                                    />
                                    <label for="period_yes" class="text-sm">
                                        {{ $t('public.yes') }}
                                    </label>
                                </div>
                                <div class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="modelValue.campaign_period"
                                        inputId="period_no"
                                        :value="false"
                                        :invalid="errors?.campaign_period"
                                    />
                                    <label for="period_no" class="text-sm">
                                        {{ $t('public.no') }}
                                    </label>
                                </div>
                            </div>
                            <InputError :message="errors?.campaign_period?.[0] ?? ''" />
                        </div>
                    </div>

                    <div
                        v-if="modelValue.campaign_period"
                        class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch"
                    >
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.set_period')"
                            for="campaign_period_range"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <DatePicker
                                v-model="modelValue.campaign_period_range"
                                selectionMode="range"
                                :manualInput="false"
                                dateFormat="dd/mm/yy"
                                placeholder="dd/mm/yyyy - dd/mm/yyyy"
                                class="w-full md:min-w-80"
                                :invalid="!!errors?.campaign_period_range"
                            />
                            <InputError :message="errors?.campaign_period_range?.[0] ?? ''" />
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel class="min-w-40 flex items-center gap-2">
                            {{ $t('public.eligible_service') }}
                            <IconAlertTooltip :message="$t('public.eligible_service_tooltip')" />
                        </FormLabel>

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="flex items-center gap-5 h-11">
                                <div class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="modelValue.eligible_service"
                                        inputId="all_services"
                                        value="all_services"
                                        :invalid="!!errors?.eligible_service"
                                    />
                                    <label for="all_services" class="text-sm">
                                        {{ $t('public.all_services') }}
                                    </label>
                                </div>
                                <div class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="modelValue.eligible_service"
                                        inputId="dine_in_only"
                                        value="dine_in_only"
                                        :invalid="!!errors?.eligible_service"
                                        disabled
                                    />
                                    <label for="dine_in_only" class="text-sm">
                                        {{ $t('public.dine_in_only') }}
                                    </label>
                                </div>
                                <div class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="modelValue.eligible_service"
                                        inputId="takeaway_only"
                                        value="takeaway_only"
                                        :invalid="!!errors?.eligible_service"
                                        disabled
                                    />
                                    <label for="takeaway_only" class="text-sm">
                                        {{ $t('public.takeaway_only') }}
                                    </label>
                                </div>
                            </div>
                            <InputError :message="errors?.eligible_service?.[0] ?? ''" />
                        </div>
                    </div>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold flex gap-1 items-start">
                        {{ $t('public.discount_value') }} <span class="text-red-500 text-xs pt-1">*</span>
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
                        {{ $t('public.discount_value_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="flex flex-col gap-5 self-stretch p-5">
                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.discount_rate')"
                            for="discount_rate"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch w-full md:max-w-80">
                            <div class="flex gap-3 items-center w-full">
                                <div class="relative w-full">
                                    <!-- RM prefix only when type = amount -->
                                    <div
                                        v-if="modelValue.discount_type === 'amount'"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 z-10 text-sm"
                                    >
                                        RM
                                    </div>

                                    <InputNumber
                                        v-model="modelValue.discount_rate"
                                        placeholder="XX"
                                        inputId="discount_rate"
                                        fluid
                                        :min-fraction-digits="modelValue.discount_type === 'percentage' ? 0 : 2"
                                        :max-fraction-digits="modelValue.discount_type === 'percentage' ? 0 : 2"
                                        :max="modelValue.discount_type === 'percentage' ? 100 : null"
                                        :invalid="!!errors?.discount_rate"
                                        :inputClass="[
                                            'w-full',
                                            modelValue.discount_type === 'amount' ? 'pl-10' : 'pr-8'
                                        ]"
                                    />

                                    <!-- % suffix only when type = percentage -->
                                    <div
                                        v-if="modelValue.discount_type === 'percentage'"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 z-10 text-sm"
                                    >
                                        %
                                    </div>
                                </div>

                                <OptionCard
                                    value="percentage"
                                    :active="modelValue.discount_type === 'percentage'"
                                    @select="modelValue.discount_type = $event"
                                >
                                    <IconPercentage :size="20" stroke-width="1.5" />
                                </OptionCard>

                                <OptionCard
                                    value="amount"
                                    :active="modelValue.discount_type === 'amount'"
                                    @select="modelValue.discount_type = $event"
                                >
                                    <IconCurrencyDollar :size="20" stroke-width="1.5" />
                                </OptionCard>
                            </div>
                            <InputError :message="errors?.discount_rate?.[0] ?? ''" />
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel class="min-w-40 flex items-center gap-2">
                            {{ $t('public.discount_limit') }}
                            <IconAlertTooltip :message="$t('public.discount_limit_caption')" />
                        </FormLabel>

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div class="flex items-center gap-5 h-11">
                                <div class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="modelValue.discount_limit"
                                        inputId="limit_yes"
                                        :value="true"
                                        :invalid="!!errors?.discount_limit"
                                        :disabled="modelValue.discount_type === 'amount'"
                                    />
                                    <label for="limit_yes" class="text-sm">
                                        {{ $t('public.yes') }}
                                    </label>
                                </div>
                                <div class="flex items-center gap-3">
                                    <RadioButton
                                        v-model="modelValue.discount_limit"
                                        inputId="limit_no"
                                        :value="false"
                                        :invalid="!!errors?.discount_limit"
                                        :disabled="modelValue.discount_type === 'amount'"
                                    />
                                    <label for="limit_no" class="text-sm">
                                        {{ $t('public.no') }}
                                    </label>
                                </div>
                                <InputError :message="errors?.discount_limit?.[0] ?? ''" />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                        <FormLabel
                            class="min-w-40"
                            :label="$t('public.capped_at')"
                            for="capped_amount"
                        />

                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <div v-if="modelValue.discount_limit" class="relative w-full">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                                    RM
                                </div>

                                <InputNumber
                                    v-model="modelValue.capped_amount"
                                    placeholder="XX"
                                    inputId="capped_amount"
                                    inputClass="w-full md:min-w-80 pl-10"
                                    fluid
                                    :invalid="!!errors?.capped_amount"
                                    :disabled="!modelValue.discount_limit"
                                />
                            </div>
                            <div v-else class="w-full">
                                <InputText
                                    id="name"
                                    class="w-full md:min-w-80"
                                    :value="$t('public.no_capped_amount')"
                                    placeholder="RM XX"
                                    disabled
                                />
                            </div>
                            <InputError :message="errors?.capped_amount?.[0] ?? ''" />
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
