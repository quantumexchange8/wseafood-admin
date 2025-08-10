<script setup>
import {IconTrash} from "@tabler/icons-vue";
import {useConfirm} from "primevue/useconfirm";
import {trans} from "laravel-vue-i18n";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    product: Object,
});

const confirm = useConfirm();

const requireConfirmation = (action_type) => {
    const messages = {
        delete_product: {
            group: 'headless-error',
            header: trans('public.delete_confirm_header'),
            message: trans('public.delete_confirm_message'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.delete'),
            action: () => {
                router.delete(route('product.destroy'), {
                    data: {
                        id: props.product.id,
                    },
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
    <div
        class="flex items-center justify-center rounded-full grow-0 shrink-0 select-none cursor-pointer text-surface-500 dark:text-surface-400 hover:text-red-500"
        @click.stop="requireConfirmation('delete_product')"
        v-tooltip.top="$t('public.delete')"
    >
        <IconTrash :size="16" :stroke-width="1.5" />
    </div>
</template>
