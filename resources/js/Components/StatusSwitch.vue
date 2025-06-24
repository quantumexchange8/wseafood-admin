<script setup>
import { ToggleSwitch } from 'primevue';
import { router } from '@inertiajs/vue3';
import { useConfirm } from "primevue/useconfirm";
import { trans } from 'laravel-vue-i18n';

const props = defineProps({
    data: Object,
    path: String,
    popup: {
        type: Boolean,
        default: false,
    },
});

const confirm = useConfirm();

const requireConfirmation = (action_type) => {
    const messages = {
        update_status: {
            group: 'headless-primary',
            header: trans('public.change_status_header'),
            message: trans('public.change_status_message'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.yes'),
            action: () => {
                router.post(route(props.path), {
                    id: props.data.id,
                    status: props.data.status,
                });
            }
        },
        update_popup: {
            group: 'headless-primary',
            header: trans('public.change_popup_header'),
            message: trans('public.change_popup_message'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.yes'),
            action: () => {
                router.post(route(props.path), {
                    id: props.data.id,
                    status: props.data.can_popup,
                });
            }
        },
    };

    const { group, header, message, actionType, cancelButton, acceptButton, action } = messages[action_type];

    confirm.require({
        group,
        header,
        actionType,
        message,
        cancelButton,
        acceptButton,
        accept: action
    });
};
</script> 

<template>
    <ToggleSwitch
        v-if="popup"
        :model-value="data.can_popup"
        :true-value="1"
        :false-value="0"
        class="flex items-center"
        @click="requireConfirmation('update_popup')"
        readonly
    />
    <ToggleSwitch
        v-else
        :model-value="data.status"
        true-value="active"
        false-value="inactive"
        class="flex items-center"
        @click="requireConfirmation('update_status')"
        readonly
    />
</template>