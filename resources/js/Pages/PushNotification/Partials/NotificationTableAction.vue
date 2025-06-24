<script setup>
import {router} from "@inertiajs/vue3";
import {IconBellRinging, IconPencil, IconTrash} from "@tabler/icons-vue";
import {Button} from "primevue";
import {useConfirm} from "primevue/useconfirm";
import {trans} from "laravel-vue-i18n";

const props = defineProps({
    notification: Object
});

const confirm = useConfirm();

const requireConfirmation = (action_type) => {
    const messages = {
        push_notification: {
            group: 'headless-primary',
            header: trans('public.push_notification_header'),
            message: trans('public.push_notification_message'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.push_now'),
            action: () => {
                router.post(route('notification.push_notification', props.notification.id));
            }
        },
        delete_notification: {
            group: 'headless-error',
            header: trans('public.delete_confirm_header'),
            message: trans('public.delete_confirm_message'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.delete'),
            action: () => {
                router.delete(route('notification.destroy'), {
                    data: {
                        id: props.notification.id,
                    },
                });
            }
        }
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
    <div class="flex items-center gap-2">
        <Button
            type="button"
            severity="secondary"
            outlined
            size="small"
            class="rounded-full"
            @click="requireConfirmation('push_notification')"
        >
            <template #icon>
                <IconBellRinging :size="14" stroke-width="1.5"/>
            </template>
        </Button>

        <Button
            type="button"
            severity="secondary"
            outlined
            size="small"
            class="rounded-full"
            @click="router.visit(route('notification.edit', notification.id))"
        >
            <template #icon>
                <IconPencil :size="14" stroke-width="1.5"/>
            </template>
        </Button>

        <Button
            type="button"
            severity="danger"
            outlined
            size="small"
            class="rounded-full"
            @click="requireConfirmation('delete_notification')"
        >
            <template #icon>
                <IconTrash :size="16" stroke-width="1.5" />
            </template>
        </Button>
    </div>
</template>
