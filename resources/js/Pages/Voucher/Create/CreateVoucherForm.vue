<script setup>
import {Stepper, StepList, StepPanels, Step, StepPanel, Button} from "primevue";
import {ref} from "vue";
import General from "@/Pages/Voucher/Create/Step/General.vue";
import FooterForm from "@/Components/FooterForm.vue";
import Redemption from "@/Pages/Voucher/Create/Step/Redemption.vue";
import Setting from "@/Pages/Voucher/Create/Step/Setting.vue";
import toast from "@/Composables/toast.js";
import {IconLoader2} from "@tabler/icons-vue";
import {router} from "@inertiajs/vue3";

const form = ref({
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
    },
});

const formProcessing = ref(false);
const errors = ref({})

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

        router.visit(route('voucher.index'))
    } catch (error) {
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
                                        await submitForm()
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
</template>
