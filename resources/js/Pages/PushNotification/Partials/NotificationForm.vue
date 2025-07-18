<script setup>
import { Card, Button, InputText, RadioButton, Select, DatePicker } from 'primevue';
import { useForm, usePage} from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputError from '@/Components/InputError.vue';
import TipTapEditor from '@/Components/TipTapEditor.vue';
import {useConfirm} from "primevue/useconfirm";
import {trans} from "laravel-vue-i18n";
import dayjs from "dayjs";

const props = defineProps({
    notification: Object,
});

const availableLocales = JSON.parse(usePage().props.availableLocales);
const scheduleType = ref(['immediately', 'scheduled']);
const showDatepicker = ref(false);

const form = useForm({
    id: props.notification?.id ?? '',
    title: props.notification?.title ?? {},
    message: props.notification?.message ?? {},
    content: props.notification?.content ?? '',
    schedule_type: props.notification?.schedule_type ?? '',
    schedule_datetime: props.notification?.schedule_datetime ?? null,
    status: props.notification?.status ?? '',
    push_now: false,
});

const submitForm = () => {
    const path = ref('notification.store');
    if(props.notification) {
        path.value = 'notification.update';
    }

    if (form.schedule_datetime) {
        const formatted = dayjs(form.schedule_datetime);
        if (formatted.isValid()) {
            form.schedule_datetime = formatted.format('YYYY-MM-DD HH:mm:ss');
        } else {
            form.schedule_datetime = null; // or handle accordingly
        }
    }

    form.post(route(path.value, props.notification?.id), {
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
        },
    })
};

const pushConfirmation = () => {
    if (form.schedule_type === 'immediately') {
        requireConfirmation('push_notification');
    } else {
        form.push_now = false;
        submitForm();
    }
};

const confirm = useConfirm();

const requireConfirmation = (action_type) => {
    const messages = {
        push_notification: {
            group: 'headless-primary',
            header: trans('public.push_notification_header'),
            message: trans('public.push_notification_message'),
            cancelButton: trans('public.not_now'),
            acceptButton: trans('public.push_now'),
            action: () => {
                form.push_now = true;
                submitForm();
            },
            rejectAction: () => {
                form.push_now = false;
                submitForm();
            }
        },
    };

    const { group, header, message, actionType, cancelButton, acceptButton, action, rejectAction } = messages[action_type];

    confirm.require({
        group,
        header,
        actionType,
        message,
        cancelButton,
        acceptButton,
        accept: action,
        reject: rejectAction
    });
};

function formatSQLDateTimeToJS(sqlDateTime) {
    if (!sqlDateTime) return null;
    const parsed = dayjs(sqlDateTime, 'YYYY-MM-DD HH:mm:ss');
    return parsed.isValid() ? parsed.toDate() : null;
}

watch(() => form.schedule_type, () => {
    if(form.schedule_type === 'scheduled') {
        showDatepicker.value = true;
    }else {
        showDatepicker.value = false;
        form.schedule_datetime = null;
    }
});

watch(()=> props.notification, () => {
    if(props.notification?.schedule_type === 'scheduled') {
        showDatepicker.value = true;
    }

    form.schedule_datetime = formatSQLDateTimeToJS(props.notification?.schedule_datetime);
},{immediate:true, deep:true});

</script>

<template>
    <form @submit.prevent="submitForm" class="flex flex-col items-start gap-4 self-stretch">
        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.notification_detail') }}
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
                        {{ $t('public.notification_detail_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="p-5 grid grid-cols-4 items-center gap-5 self-stretch">
                    <template
                        v-for="lang in availableLocales"
                        :key="lang"
                    >
                        <div class="w-full flex items-center gap-1">
                            <label :for="`name-${lang.value}`" class="text-sm">
                                {{ $t('public.title') }} ({{ lang.label }})
                            </label>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <div class="col-span-2 flex flex-col gap-1">
                            <InputText
                                v-model="form.title[lang.value]"
                                :id="`name-${lang.value}`"
                                class="w-full"
                                :placeholder="$t('public.notification_title_placeholder')"
                                :invalid="!!form.errors[`title.${lang.value}`]"
                            />
                            <InputError :message="form.errors[`title.${lang.value}`]" />
                        </div>
                        <div></div>
                    </template>

                    <template
                        v-for="lang in availableLocales"
                        :key="lang"
                    >
                        <div class="w-full flex items-center gap-1">
                            <label :for="`message-${lang.value}`" class="text-sm">
                                {{ $t('public.message') }} ({{ lang.label }})
                            </label>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <div class="col-span-2 flex flex-col gap-1">
                            <InputText
                                v-model="form.message[lang.value]"
                                :id="`message-${lang.value}`"
                                class="w-full"
                                :placeholder="$t('public.notification_message_placeholder')"
                                :invalid="!!form.errors[`message.${lang.value}`]"
                            />
                            <InputError :message="form.errors[`message.${lang.value}`]" />
                        </div>
                        <div></div>
                    </template>

                    <div class="w-full flex items-center gap-1">
                        <div class="text-sm">
                            {{ $t('public.schedule_type') }}
                        </div>
                        <div class="text-xs text-red-500">
                            *
                        </div>
                    </div>
                    <div class="col-span-2 flex flex-col gap-1">
                        <Select
                            v-model="form.schedule_type"
                            :options="scheduleType"
                            :placeholder="$t('public.select_schedule_type')"
                            :invalid="!!form.errors.schedule_type"
                            class="w-full"
                            showClear
                        >
                            <template #value="slotProps">
                                <div v-if="slotProps.value" class="flex items-center">
                                    {{ $t(`public.${slotProps.value}`) }}
                                </div>
                                <span v-else>{{ slotProps.placeholder }}</span>
                            </template>

                            <template #option="slotProps">
                                <div>
                                    {{ $t(`public.${slotProps.option}`) }}
                                </div>
                            </template>
                        </Select>
                        <InputError :message="form.errors.schedule_type" />
                    </div>
                    <div></div>

                    <template v-if="showDatepicker">
                        <div class="w-full flex items-center gap-1">
                            <div class="text-sm">
                                {{ $t('public.schedule_time') }}
                            </div>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <div class="col-span-2 flex flex-col gap-1">
                            <DatePicker
                                id="datepicker"
                                v-model="form.schedule_datetime"
                                :invalid="!!form.errors.schedule_datetime"
                                :placeholder="$t('public.date')"
                                showTime
                                hourFormat="24"
                                dateFormat="yy-mm-dd"
                                :minDate="new Date()"
                                fluid
                            />
                            <InputError :message="form.errors.schedule_datetime" />
                        </div>
                        <div></div>
                    </template>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.content') }}
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
                        {{ $t('public.content_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <TipTapEditor
                    v-model="form.content"
                    :invalid="form.errors.content"
                />
                <InputError :message="form.errors.content" />
            </template>
        </Card>

        <div class="w-full mt-1 px-5 py-4 flex justify-between items-center border-t border-solid border-primary-200 dark:border-primary-900/40 bg-white shadow-sm dark:bg-surface-900">
            <Button
                type="button"
                severity="secondary"
                outlined
                :label="$t('public.cancel')"
                @click="() => $inertia.visit(route('notification.index'))"
                :disabled="form.processing"
            />
            <Button
                type="button"
                severity="primary"
                :label="$t('public.submit')"
                @click="pushConfirmation"
                :disabled="form.processing"
            />
        </div>
    </form>
</template>
