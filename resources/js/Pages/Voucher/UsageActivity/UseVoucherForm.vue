<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Button, Card, Select, Image, InputText} from "primevue";
import InputError from "@/Components/InputError.vue";
import FormLabel from "@/Components/FormLabel.vue";
import {IconLoader2} from "@tabler/icons-vue";
import FooterForm from "@/Components/FooterForm.vue";
import {ref, watch} from "vue";
import debounce from "lodash/debounce.js";
import toast from "@/Composables/toast.js";
import LoadingMask from "@/Pages/Voucher/UsageActivity/LoadingMask.vue";

const form = ref({
    user_id: '',
    voucher_id: '',
    remarks: ''
})

const members = ref([])
const searchKeyword = ref('')
const selectedMember = ref(null)
const loadingMembers = ref(false)

let controller

const getMembers = async () => {
    if (controller) controller.abort()
    controller = new AbortController()
    loadingMembers.value = true
    try {
        const response = await axios.get(route('getMembers'), {
            params: { keyword: searchKeyword.value },
            signal: controller.signal
        })
        members.value = response.data
    } catch (error) {
        if (error.code === 'ERR_CANCELED') {
            console.log('Request canceled')
        } else {
            console.error(error)
        }
    } finally {
        loadingMembers.value = false
    }
}

watch(
    searchKeyword,
    debounce(() => {
        if (searchKeyword.value) {
            getMembers()
        } else {
            members.value = []
        }
    }, 100)
);

watch(selectedMember, (newVal) => {
    if (newVal && typeof newVal === 'object') {
        selectedVoucher.value = null
        getUserVouchers()
    }
})

const userVouchers = ref([])
const selectedVoucher = ref(null)
const loadingVouchers = ref(false)

const getUserVouchers = async () => {
    loadingVouchers.value = true
    try {
        const response = await axios.get(route('getUserVouchers', {
            user_id: selectedMember.value.id
        }));
        userVouchers.value = response.data;

        if (userVouchers.value.length === 1) {
            console.log(userVouchers.value)
            selectVoucher(userVouchers.value[0].voucher);
        }
    } catch (error) {
        console.error('Error fetching vouchers:', error);
    } finally {
        loadingVouchers.value = false
    }
}

const selectVoucher = (voucher) => {
    selectedVoucher.value = voucher
}

const formProcessing = ref(false);
const errors = ref({})
const bc = new BroadcastChannel('voucher-usage-updates');

const submitForm = async () => {
    form.value.user_id = selectedMember.value?.id;
    form.value.voucher_id = selectedVoucher.value?.id;

    try {
        formProcessing.value = true;

        const response = await axios.post(route('voucher.useVoucher'), form.value)
        errors.value = {}

        toast.add({
            type: response.data.type,
            title: response.data.title,
            message: response.data.message,
        });

        form.value = {
            user_id: '',
            voucher_id: '',
            remarks: '',
        }

        selectedMember.value = null
        selectedVoucher.value = null
        searchKeyword.value = ''
        members.value = []
        userVouchers.value = []

        // Notify other tabs
        bc.postMessage({ type: 'voucher_used' });
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
    <AuthenticatedLayout :title="$t('public.voucher_usage')">
        <div class="flex flex-col items-start gap-5 flex-1">
            <div class="flex flex-col items-start self-stretch">
                <h1 class="text-2xl font-bold">
                    {{ $t('public.voucher_usage') }}
                </h1>
                <div class="self-stretch text-sm text-surface-500">
                    {{ $t('public.voucher_usage_caption') }}
                </div>
            </div>

            <Card class="w-full">
                <template #title>
                    <div class="px-5 py-3 flex justify-between items-center self-stretch">
                        <div class="text-lg font-bold flex gap-1 items-start">
                            {{ $t('public.apply_voucher') }} <span class="text-red-500 text-xs pt-1">*</span>
                        </div>
                        <div class="font-normal italic text-xs text-gray-400">
                            {{ $t('public.apply_voucher_caption') }}
                        </div>
                    </div>
                </template>
                <template #content>
                    <form @submit.prevent="submitForm" class="flex flex-col gap-5 self-stretch p-5">
                        <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                            <FormLabel
                                class="min-w-40"
                                :label="$t('public.select_member')"
                                for="member"
                            />

                            <div class="flex flex-col items-start gap-1 self-stretch">
                                <Select
                                    v-model="selectedMember"
                                    editable
                                    input-id="member"
                                    input-class="placeholder:text-surface-400 dark:placeholder:text-surface-700"
                                    :options="members"
                                    optionLabel="full_name"
                                    :placeholder="$t('public.search_member')"
                                    class="w-full md:min-w-80"
                                    :loading="loadingMembers"
                                    @input="searchKeyword = $event.target.value"
                                    :invalid="!!errors?.user_id"
                                >
                                    <template #value="{value, placeholder}">
                                        <div v-if="value">
                                            {{ value.full_name }}
                                        </div>
                                        <span v-else>{{ placeholder }}</span>
                                    </template>
                                    <template #option="{option}">
                                        <div class="flex flex-col">
                                            <span>{{ option.full_name }}</span>
                                            <span class="text-xs text-surface-500">{{ option.phone_number }}</span>
                                        </div>
                                    </template>
                                </Select>
                                <InputError :message="errors?.user_id?.[0]" />
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                            <FormLabel
                                class="min-w-40"
                                :label="$t('public.voucher')"
                                for="apply_voucher"
                            />

                            <div class="flex flex-col items-start gap-1 self-stretch w-full">
                                <!-- Empty -->
                                <div
                                    v-if="userVouchers.length === 0"
                                    class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 w-full"
                                >
                                    <div class="flex flex-col rounded-2xl border shadow-input dark:border-surface-700">
                                        <!-- Image -->
                                        <div class="rounded-tl-xl rounded-tr-xl bg-surface-200 dark:bg-surface-700 h-24 w-full"></div>
                                        <div class="flex flex-col text-sm p-3">
                                            <div class="font-bold text-surface-950 dark:text-white">
                                                {{ $t('public.no_voucher_found') }}
                                            </div>
                                            <div class="text-surface-500 dark:text-surface-400">
                                                {{ $t('public.voucher_code') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <LoadingMask
                                    v-else-if="loadingVouchers"
                                />


                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 w-full">
                                    <div
                                        v-for="redemption in userVouchers"
                                        class="flex flex-col rounded-2xl border shadow-input hover:cursor-pointer select-none transition-all duration-200"
                                        :class="selectedVoucher?.id === redemption.voucher.id
                                       ? 'border-primary bg-primary-50 dark:bg-primary-700/40'
                                       : 'dark:border-surface-700 hover:border-primary hover:bg-primary-50 hover:dark:bg-primary-700/40'"
                                        @click="selectVoucher(redemption.voucher)"
                                    >
                                        <!-- Image -->
                                        <Image
                                            :src="redemption.voucher.media[0].original_url"
                                            class="rounded-tl-2xl rounded-tr-2xl bg-surface-200 dark:bg-surface-700"
                                            image-class="h-24 rounded-tl-2xl rounded-tr-2xl object-cover w-full"
                                        />
                                        <div class="flex flex-col text-sm p-3">
                                            <div class="font-bold text-surface-950 dark:text-white">
                                                {{ redemption.voucher.voucher_name }}
                                            </div>
                                            <div class="text-surface-500 dark:text-surface-400">
                                                {{ redemption.voucher.voucher_code }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <InputError :message="errors?.voucher_id?.[0]" />
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch">
                            <FormLabel
                                class="min-w-40"
                                :label="$t('public.remark')"
                                for="remarks"
                            />

                            <div class="flex flex-col items-start gap-1 self-stretch">
                                <InputText
                                    v-model="form.remarks"
                                    id="remarks"
                                    class="w-full md:min-w-80"
                                    :placeholder="$t('public.eg_order_number')"
                                    :invalid="!!errors?.remarks"
                                />
                                <InputError :message="errors?.remarks?.[0] ?? ''" />
                            </div>
                        </div>

                        <FooterForm>
                            <div class="flex justify-between items-center">
                                <Button
                                    type="button"
                                    severity="secondary"
                                    outlined
                                    :label="$t('public.cancel')"
                                    @click="() => $inertia.visit(route('voucher.usage_activity'))"
                                    :disabled="form.processing"
                                />
                                <Button
                                    type="submit"
                                    severity="primary"
                                    :label="$t('public.submit')"
                                    :disabled="formProcessing"
                                >
                                    <IconLoader2
                                        v-if="formProcessing"
                                        size="16"
                                        stroke-width="1.5"
                                        class="animate-spin"
                                    />
                                    {{ $t('public.submit') }}
                                </Button>
                            </div>
                        </FooterForm>
                    </form>
                </template>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
