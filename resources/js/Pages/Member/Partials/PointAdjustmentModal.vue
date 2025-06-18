<script setup>
import { Dialog, Button, InputNumber, InputText } from 'primevue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputError from '@/Components/InputError.vue';
import IconAlertTooltip from '@/Components/IconAlertTooltip.vue';

const props = defineProps({
    visible: Boolean,
    member: Object,
});

const emit = defineEmits(['update:visible', 'update:pointAdjusted']);

const show = ref(false);

const form = useForm({
    adjustPoint: null,
    memberId: null,
    remark: '',
});

const submitForm = () => {
    form.memberId = props.member.id;

    form.post(route('member.adjustPoint'), {
        onSuccess: () => {
            form.reset();
            show.value = false;
            emit('update:pointAdjusted', true);
        },
    })
};

const cancelAction = () => {
    form.reset();
    
    show.value = false;
};

watch(() => props.visible, (val) => {
    show.value = val;
});

watch(show, (val) => {
    if (val !== props.visible) {
        emit('update:visible', val);
    }
});

</script>

<template>
    <Dialog
        v-model:visible="show"
        modal
        class="dialog-sm"
    >
        <template #header>
            <div class="text-lg font-bold">
                {{ $t('public.point_adjustment') }}: {{ member.full_name }}
            </div>
        </template>
        <form 
            @submit.prevent="submitForm"
            class="p-5 flex flex-col gap-5 self-stretch"
        >
            <div class="flex flex-col items-start gap-3 self-stretch">
                <label for="current-point">
                    {{ $t('public.current_point') }}
                </label>
                <div class="relative w-full">
                    <InputNumber
                        :model-value="member.point"
                        inputId="current-point"
                        readonly
                        fluid
                    />
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                        PTS
                    </span>
                </div>
            </div>
            <div class="flex flex-col items-start gap-3 self-stretch">
                <div class="flex items-center gap-2">
                    <label for="adjust-point">
                        {{ $t('public.adjust_point') }}
                    </label>
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
                    />
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                        PTS
                    </span>
                </div>
                <InputError :message="form.errors.adjustPoint" />
            </div>
            <div class="flex flex-col items-start gap-3 self-stretch">
                <div class="flex items-center gap-2">
                    <label for="remark">
                        {{ $t('public.remark') }}
                    </label>
                </div>
                <InputText
                    v-model="form.remark"
                    placeholder="e.g. Addition"
                    inputId="remark"
                    class="w-full"
                />
                <InputError :message="form.errors.remark" />
            </div>
        </form>
        <template #footer>
            <Button
                type="button"
                :label="$t('public.cancel')"
                severity="secondary"
                outlined
                @click="cancelAction"
                :disabled="form.processing"
            />
            <Button
                type="submit"
                :label="$t('public.save')"
                @click="submitForm"
                :disabled="form.processing"
            />
        </template>
    </Dialog>
</template>