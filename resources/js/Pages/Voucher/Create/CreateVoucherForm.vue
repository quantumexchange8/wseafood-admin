<script setup>
import {Badge, Drawer, Image, Stepper, StepList, StepPanels, Step, StepPanel, Button} from "primevue";
import {computed, ref} from "vue";
import General from "@/Pages/Voucher/Create/Step/General.vue";
import FooterForm from "@/Components/FooterForm.vue";
import Redemption from "@/Pages/Voucher/Create/Step/Redemption.vue";
import Setting from "@/Pages/Voucher/Create/Step/Setting.vue";
import toast from "@/Composables/toast.js";
import {IconLoader2} from "@tabler/icons-vue";
import dayjs from "dayjs";
import {generalFormat} from "@/Composables/format.js";
import {trans} from "laravel-vue-i18n";

const defaultForm = () => ({
    general: {
        voucher_type: 'value_discount',
        voucher_name: '',
        description: '',
        campaign_period: false,
        campaign_period_range: null,
        eligible_service: 'all_services',
        discount_rate: null,
        discount_type: 'percentage',
        discount_limit: true,
        capped_amount: null,
    },
    redemption: {
        claim_method: 'point_to_claim',
        point_to_claim: {
            redeem_point: null,
        },
        code_to_claim: {
            voucher_code: '',
        },
        add_for_member: {
            activation_rule: 'first_time_registration',
            event_type: 'member_birthday',
            special_holiday_date: null,
            amount_paid: null,
        },
        claim_limit: 'limited',
        voucher_limit: null,
        renew_voucher_limit: 'never_renew',
        claim_amount_per_member: null,
        renew_claim_limit: 'never_renew',
        validity_count: null,
        validity_count_type: 'day',
        requirement_type: 'no_requirement',
        min_spend_amount: null,
        valid_type: 'all_day_time',
        valid_days: null,
        valid_time: [null, null],
        can_stack: false,
    },
    setting: {
        voucher_thumbnail: null,
        voucher_thumbnail_preview: null,
        voucher_highlight: null,
    },
})

const form = ref(defaultForm());

const formProcessing = ref(false);
const errors = ref({})
const {formatAmount} = generalFormat();
const bc = new BroadcastChannel('voucher-updates');

const validateStep = async (step) => {
    try {
        formProcessing.value = true;
        let payload = {}

        if (step === 'general') {
            payload = form.value.general
        } else if (step === 'redemption') {
            payload = form.value.redemption
        } else if (step === 'setting') {
            payload = form.value.setting
        }

        await axios.post(`/voucher/create/validate/${step}`, payload, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        })
        errors.value = {}
        return true
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
        return false
    } finally {
        formProcessing.value = false;
    }
}

const toFormData = (form) => {
    return {
        ...form.general,
        ...form.redemption,
        ...form.setting,
    }
}

const submitForm = async () => {
    try {
        formProcessing.value = true;
        const payload = toFormData(form.value)

        const response = await axios.post(`/voucher/create/store`, payload, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        errors.value = {}

        toast.add({
            type: response.data.type,
            title: response.data.title,
            message: response.data.message,
        });

        form.value = defaultForm()
        visible.value = false

        bc.postMessage({ type: 'add_voucher' });
    } catch (error) {
        visible.value = false;

        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }

        if (error.response && error.response.status === 400) {
            toast.add({
                title: error.response.data.title,
                message: error.response.data.message,
                type: error.response.data.type,
            });
        }
    } finally {
        formProcessing.value = false;
    }
}

const visible = ref(false);

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
    const days = form.value.redemption.valid_days || []

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
    <div class="w-full">
        <Stepper value="1" linear>
            <StepList>
                <Step value="1">{{ $t('public.general_voucher_detail') }}</Step>
                <Step value="2">{{ $t('public.redemption_usage_condition') }}</Step>
                <Step value="3">{{ $t('public.other_voucher_setting') }}</Step>
            </StepList>
            <StepPanels>
                <StepPanel v-slot="{ activateCallback }" value="1">
                    <General v-model="form.general" :errors="errors || {}" />

                    <FooterForm>
                        <div class="flex justify-between items-center">
                            <Button
                                type="button"
                                severity="secondary"
                                outlined
                                :label="$t('public.cancel')"
                                @click="() => $inertia.visit(route('voucher.index'))"
                                :disabled="form.processing"
                            />
                            <Button
                                type="button"
                                severity="primary"
                                :label="$t('public.continue')"
                                @click="async () => {
                                    if (await validateStep('general')) activateCallback('2')
                                }"
                                :disabled="formProcessing"
                            >
                                <IconLoader2
                                    v-if="formProcessing"
                                    size="16"
                                    stroke-width="1.5"
                                    class="animate-spin"
                                />
                                {{ $t('public.continue') }}
                            </Button>
                        </div>
                    </FooterForm>
                </StepPanel>
                <StepPanel v-slot="{ activateCallback }" value="2">
                    <Redemption v-model="form.redemption" :errors="errors || {}" />

                    <FooterForm>
                        <div class="flex justify-between items-center">
                            <Button
                                type="button"
                                severity="secondary"
                                outlined
                                :label="$t('public.back')"
                                @click="() => activateCallback('1')"
                                :disabled="formProcessing"
                            />
                            <Button
                                type="button"
                                severity="primary"
                                :label="$t('public.continue')"
                                @click="async () => {
                                    if (await validateStep('redemption')) activateCallback('3')
                                }"
                                :disabled="formProcessing"
                            >
                                <IconLoader2
                                    v-if="formProcessing"
                                    size="16"
                                    stroke-width="1.5"
                                    class="animate-spin"
                                />
                                {{ $t('public.continue') }}
                            </Button>
                        </div>
                    </FooterForm>
                </StepPanel>
                <StepPanel v-slot="{ activateCallback }" value="3">
                    <Setting v-model="form.setting" :errors="errors || {}" />

                    <FooterForm>
                        <div class="flex justify-between items-center">
                            <Button
                                type="button"
                                severity="secondary"
                                outlined
                                :label="$t('public.back')"
                                @click="() => activateCallback('2')"
                                :disabled="formProcessing"
                            />
                            <Button
                                type="button"
                                severity="primary"
                                @click="async () => {
                                    if (await validateStep('setting')) {
                                        visible = true
                                        activateCallback('1')
                                    }
                                }"
                                :disabled="formProcessing"
                            >
                                <IconLoader2
                                    v-if="formProcessing"
                                    size="16"
                                    stroke-width="1.5"
                                    class="animate-spin"
                                />
                                {{ $t('public.create_voucher') }}
                            </Button>
                        </div>
                    </FooterForm>
                </StepPanel>
            </StepPanels>
        </Stepper>
    </div>

    <Drawer
        v-model:visible="visible"
        :header="$t('public.confirmation')"
        position="full"
    >
        <div class="flex flex-col w-full">
            <!-- Image -->
            <Image
                :src="form.setting.voucher_thumbnail_preview"
                class="rounded-lg bg-surface-200 dark:bg-surface-700"
                image-class="h-80 rounded-md object-contain w-full"
            />

            <!-- Step 1 -->
            <div class="flex flex-col gap-5 py-6 items-center self-stretch">
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
                            {{ $t(`public.${form['general'].voucher_type}`) }}
                        </div>
                    </div>
                    <div class="flex flex-col text-sm">
                        <div class="text-surface-500 dark:text-surface-400">
                            {{ $t('public.voucher_name') }}
                        </div>
                        <div class="font-bold text-surface-950 dark:text-white">
                            {{ form['general']?.voucher_name ? form['general'].voucher_name : '-' }}
                        </div>
                    </div>
                    <div class="flex flex-col text-sm">
                        <div class="text-surface-500 dark:text-surface-400">
                            {{ $t('public.description') }}
                        </div>
                        <div class="font-bold text-surface-950 dark:text-white">
                            {{ form['general']?.description ? form['general'].description : '-' }}
                        </div>
                    </div>
                    <div class="flex flex-col text-sm">
                        <div class="text-surface-500 dark:text-surface-400">
                            {{ $t('public.campaign_period') }}
                        </div>
                        <div class="font-bold text-surface-950 dark:text-white">
                            <div v-if="form['general']?.campaign_period">
                                {{ dayjs(form['general'].campaign_period_range[0]).format('DD/MM/YYYY') }} - {{ dayjs(form['general'].campaign_period_range[1]).format('DD/MM/YYYY') }}
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
                            {{ $t(`public.${form['general'].eligible_service}`) }}
                        </div>
                    </div>
                    <div class="flex flex-col text-sm">
                        <div class="text-surface-500 dark:text-surface-400">
                            {{ $t('public.discount_rate') }}
                        </div>
                        <div class="font-bold text-surface-950 dark:text-white">
                            <div v-if="form['general']?.discount_type === 'percentage'">
                                {{ $t('public.discount_rate_capped', {percent: form['general']?.discount_rate, amount: form['general']?.capped_amount}) }}
                            </div>
                            <div v-else>
                                {{ formatAmount(form['general'].discount_rate, 2, 'RM ') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2-->
            <div class="flex flex-col gap-5 py-6 items-center self-stretch">
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
                            {{ $t(`public.${form['redemption'].claim_method}`) }}
                        </div>
                    </div>

                    <div class="flex flex-col text-sm">
                        <div class="text-surface-500 dark:text-surface-400">
                            <span v-if="form['redemption'].claim_method === 'point_to_claim'">{{ $t('public.point_to_redeem') }}</span>
                            <span v-else-if="form['redemption'].claim_method === 'code_to_claim'">{{ $t('public.voucher_code') }}</span>
                            <span v-else-if="form['redemption'].claim_method === 'add_for_member'">{{ $t('public.activation_rule') }}</span>
                        </div>
                        <div class="font-bold text-surface-950 dark:text-white">
                            <!-- Point -->
                            <div v-if="form['redemption'].claim_method === 'point_to_claim'">
                                {{ form['redemption']['point_to_claim']?.redeem_point ? form['redemption']['point_to_claim'].redeem_point : '-' }}
                            </div>

                            <!-- Code -->
                            <div v-else-if="form['redemption'].claim_method === 'code_to_claim'">
                                {{ form['redemption']['code_to_claim']?.voucher_code ? form['redemption']['code_to_claim']?.voucher_code : '-' }}
                            </div>

                            <!-- Auto add -->
                            <div v-else-if="form['redemption'].claim_method === 'add_for_member'">
                                <div v-if="form['redemption']['add_for_member'].activation_rule === 'first_time_registration'">
                                    {{ $t('public.first_time_registration') }}
                                </div>
                                <div v-else-if="form['redemption']['add_for_member'].activation_rule === 'event_based'">
                                    <span v-if="form['redemption']['add_for_member'].event_type === 'member_birthday'">{{ form['redemption']['add_for_member']?.event_type ? $t(`public.${form['redemption']['add_for_member']?.event_type}`) : '-' }}</span>
                                    <span v-else>{{ form['redemption']['add_for_member']?.event_type ? $t(`public.${form['redemption']['add_for_member']?.event_type}`) : '-' }} - {{ form['redemption']['add_for_member']?.special_holiday_date ? dayjs(form['redemption']['add_for_member']?.special_holiday_date).format('DD/MM/YYYY') : '-' }}</span>
                                </div>
                                <div v-else-if="form['redemption']['add_for_member'].activation_rule === 'amount_paid'">
                                    {{ $t('public.spend') }} RM {{ formatAmount(form['redemption']['add_for_member']?.amount_paid) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col text-sm">
                        <div class="text-surface-500 dark:text-surface-400">
                            {{ $t('public.limit_per_voucher') }}
                        </div>
                        <div class="font-bold text-surface-950 dark:text-white">
                            <div v-if="form['redemption'].claim_limit">
                                {{ formatAmount(form['redemption'].voucher_limit, 0, '') }} ({{ $t(`public.${form['redemption'].renew_voucher_limit}`) }})
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
                            <div v-if="form['redemption'].claim_limit">
                                {{ formatAmount(form['redemption'].claim_amount_per_member, 0, '') }} ({{ $t(`public.${form['redemption'].renew_claim_limit}`) }})
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
                            {{ formatAmount(form['redemption'].validity_count, 0, '') }} {{ $t(`public.valid_type_${form['redemption'].validity_count_type}`) }}
                        </div>
                    </div>

                    <div class="flex flex-col text-sm">
                        <div class="text-surface-500 dark:text-surface-400">
                            {{ $t('public.min_requirement') }}
                        </div>
                        <div class="font-bold text-surface-950 dark:text-white">
                            <div v-if="form['redemption'].requirement_type === 'no_requirement'">
                                {{ $t('public.no_requirement') }}
                            </div>
                            <div v-else>
                                {{ $t('public.min_spend') }} {{ formatAmount(form['redemption'].min_spend_amount, 2, 'RM ') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col text-sm">
                        <div class="text-surface-500 dark:text-surface-400">
                            {{ $t('public.valid_day_time') }}
                        </div>
                        <div class="font-bold text-surface-950 dark:text-white flex gap-1">
                            {{ $t(`public.${form['redemption'].valid_type}`) }}
                            <span>-</span>
                            <div v-if="form['redemption'].valid_type === 'specific_day'" class="text-surface-500 dark:text-surface-400 font-normal">
                                {{ formattedValidDays }}
                            </div>
                            <div v-else-if="form['redemption'].valid_type === 'specific_time'" class="text-surface-500 dark:text-surface-400 font-normal">
                                {{ dayjs(form['redemption'].valid_time[0]).format('HH:mm') }} - {{ dayjs(form['redemption'].valid_time[1]).format('HH:mm') }}
                            </div>
                            <div v-else class="text-surface-500 dark:text-surface-400 font-normal">
                                {{ formattedValidDays }} |
                                {{ dayjs(form['redemption'].valid_time[0]).format('HH:mm') }} - {{ dayjs(form['redemption'].valid_time[1]).format('HH:mm') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col text-sm">
                        <div class="text-surface-500 dark:text-surface-400">
                            {{ $t('public.stacking_rule') }}
                        </div>
                        <div class="font-bold text-surface-950 dark:text-white">
                            {{ form['redemption'].can_stack ? $t('public.stackable') : $t('public.not_stackable') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="flex flex-col gap-5 py-6 items-center self-stretch">
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
                    <div class="prose dark:prose-invert" v-html="form['setting'].voucher_highlight"></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center border-t-2 dark:border-surface-700 pt-5">
                <Button
                    type="button"
                    severity="secondary"
                    outlined
                    :label="$t('public.cancel')"
                    @click="visible = false"
                    :disabled="formProcessing"
                />
                <Button
                    type="button"
                    severity="primary"
                    @click="submitForm"
                    :disabled="formProcessing"
                >
                    <IconLoader2
                        v-if="formProcessing"
                        size="16"
                        stroke-width="1.5"
                        class="animate-spin"
                    />
                    {{ $t('public.create_voucher') }}
                </Button>
            </div>
        </div>
    </Drawer>
</template>
