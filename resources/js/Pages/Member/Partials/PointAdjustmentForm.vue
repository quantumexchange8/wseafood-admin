<script setup>
import { InputNumber, InputText } from 'primevue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import IconAlertTooltip from '@/Components/IconAlertTooltip.vue';
import {generalFormat} from "@/Composables/format.js";
import InputLabel from "@/Components/InputLabel.vue";

const props = defineProps({
    member: Object,
});

const form = useForm({
    adjustPoint: null,
    memberId: props.member.id,
    remark: '',
});

const emit = defineEmits(['update:visible']);
const {formatAmount} = generalFormat();

const submitForm = () => {
    form.post(route('member.adjustPoint'), {
        onSuccess: () => {
            form.reset();
            emit('update:visible', false);
        },
    })
};

defineExpose({
    submitForm
});
</script>

<template>
    <form
        @submit.prevent="submitForm"
        class="flex flex-col gap-5 self-stretch"
    >
        <div class="px-5 pt-5 flex items-center gap-5 justify-between self-stretch bg-gradient-to-b from-orange-50 to-white">
            <div class="flex flex-col items-start">
                <div class="text-lg font-bold">
                    {{ member.full_name }}
                </div>
                <div class="text-sm text-surface-500">
                    {{ member.id_number }}
                </div>
            </div>
            <div class="text-lg font-bold">
                {{ formatAmount(member.point, 0, '') }} PTS
            </div>
        </div>
        <div class="flex flex-col gap-5 items-center self-stretch px-5 pb-5">
            <div class="flex flex-col items-start gap-3 self-stretch">
                <div class="flex items-center gap-2">
                    <InputLabel
                        for="adjust-point"
                        :value="$t('public.adjust_point')"
                    />
                    <IconAlertTooltip :message="$t('public.point_adjustment_message')" />
                </div>
                <div class="relative w-full">
                    <InputNumber
                        v-model="form.adjustPoint"
                        placeholder="e.g. 1"
                        :maxFractionDigits="0"
                        inputClass="pr-12 w-full"
                        inputId="adjust-point"
                        fluid
                        :invalid="!!form.errors.adjustPoint"
                    />
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                        PTS
                    </span>
                </div>
                <InputError :message="form.errors.adjustPoint" />
            </div>
            <div class="flex flex-col items-start gap-3 self-stretch">
                <div class="flex items-center gap-2">
                    <InputLabel
                        for="remark"
                        :value="$t('public.remark')"
                    />
                </div>
                <InputText
                    v-model="form.remark"
                    :placeholder="$t('public.point_adjustment_placeholder')"
                    inputId="remark"
                    class="w-full"
                    :invalid="!!form.errors.remark"
                />
                <InputError :message="form.errors.remark" />
            </div>
        </div>
    </form>
</template>
