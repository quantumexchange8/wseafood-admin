<script setup>
import {Button, Dialog, Image, InputText} from "primevue";
import {IconLoader2} from "@tabler/icons-vue";
import {ref} from "vue";
import {generalFormat} from "@/Composables/format.js";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import toast from "@/Composables/toast.js";
import dayjs from "dayjs";

const props = defineProps({
    redemption: Object
});

const {formatAmount} = generalFormat();
const emit = defineEmits(['update:visible', 'updated:redemption']);

const form = ref({
    user_id: '',
    voucher_id: '',
    remarks: ''
});

const formProcessing = ref(false);
const errors = ref({})
const bc = new BroadcastChannel('voucher-usage-updates');

const submitForm = async () => {
    form.value.user_id = props.redemption.user_id;
    form.value.voucher_id = props.redemption.voucher_id;

    try {
        formProcessing.value = true;

        const response = await axios.post(route('voucher.useVoucher'), form.value)
        errors.value = {}

        toast.add({
            type: response.data.type,
            title: response.data.title,
            message: response.data.message,
        });

        closeDialog();
        emit('updated:redemption', response.data.data);

        form.value = {
            user_id: '',
            voucher_id: '',
            remarks: '',
        }

        // Notify other tabs
        bc.postMessage({type: 'voucher_used'});
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }

        if (error.response && error.response.status === 400) {
            closeDialog();

            form.value = {
                user_id: '',
                voucher_id: '',
                remarks: '',
            }

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

const closeDialog = () => {
    emit('update:visible', false);
}
</script>

<template>
    <form class="flex flex-col gap-5 items-center self-stretch">
        <div class="px-5 pt-5 flex items-center gap-5 justify-between self-stretch bg-gradient-to-b from-primary-50 to-white dark:from-primary-950/40 dark;to-surface-900">
            <div class="flex flex-col items-start">
                <div class="text-lg font-bold">
                    {{ redemption.user.full_name }}
                </div>
                <div class="text-sm text-surface-500">
                    {{ redemption.user.phone_number }}
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-5 items-center self-stretch px-5 pb-5">
            <div class="flex flex-col gap-1 items-start w-full">
                <div class="text-sm font-bold">
                    {{ $t('public.voucher') }}
                </div>
                <div class="flex flex-col rounded-2xl border shadow-input dark:border-surface-700 w-full">
                    <!-- Image -->
                    <Image
                        :src="redemption.voucher.media[0].original_url"
                        class="rounded-tl-2xl rounded-tr-2xl bg-surface-200 dark:bg-surface-700"
                        image-class="rounded-tl-2xl rounded-tr-2xl object-cover w-full"
                    />
                    <div class="flex flex-col gap-1 text-sm p-3">
                        <div class="font-bold text-surface-950 dark:text-white">
                            {{ redemption.voucher.voucher_name }} <span class="text-surface-500 dark:text-surface-400 font-normal">({{ redemption.voucher.voucher_code }})</span>
                        </div>

                        <div class="px-2 py-1 flex gap-2 items-center rounded-lg bg-surface-200 dark:bg-surface-800 text-xs w-fit font-medium text-surface-500 dark:text-surface-300">
                            {{ $t('public.expired_on', {date: dayjs(redemption.expired_at).format('DD/MM/YYYY')}) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-start gap-3 self-stretch">
                <div class="flex items-center gap-2">
                    <InputLabel
                        for="remark"
                        :value="$t('public.remark')"
                    />
                </div>
                <InputText
                    v-model="form.remarks"
                    :placeholder="$t('public.point_adjustment_placeholder')"
                    inputId="remark"
                    class="w-full"
                    :invalid="!!errors?.remarks"
                />
                <InputError :message="errors?.remarks?.[0] ?? ''" />
            </div>

            <div class="flex items-center justify-between gap-3 w-full pt-3">
                <Button
                    type="button"
                    :label="$t('public.cancel')"
                    severity="secondary"
                    outlined
                    class="w-full"
                    @click.prevent="closeDialog"
                    :disabled="form.processing"
                />
                <Button
                    type="submit"
                    severity="primary"
                    class="w-full font-bold"
                    @click="submitForm"
                    :disabled="formProcessing"
                >
                    <IconLoader2
                        v-if="formProcessing"
                        size="16"
                        stroke-width="1.5"
                        class="animate-spin"
                    />
                    {{ $t('public.save') }}
                </Button>
            </div>
        </div>
    </form>
</template>
