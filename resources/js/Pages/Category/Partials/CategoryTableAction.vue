<script setup>
import { router } from '@inertiajs/vue3';
import { IconPencil, IconTrash } from '@tabler/icons-vue';
import { Button } from 'primevue';
import { useConfirm } from "primevue/useconfirm";
import { trans } from 'laravel-vue-i18n';

const props = defineProps({
    category: Object,
});

const confirm = useConfirm();

const requireConfirmation = (action_type) => {
    const messages = {
        delete_category: {
            group: 'headless-error',
            header: trans('public.delete_confirm_header'),
            message: trans('public.delete_confirm_message'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.delete'),
            action: () => {
                router.delete(route('category.destroy'), {
                    data: {
                        id: props.category.id,
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
    <div class="flex items-center gap-3">
        <Button
            type="button"
            severity="secondary"
            outlined
            size="small"
            class="rounded-full"
            icon="IconPencil"
            @click="router.visit(route('category.edit', category.id))"
        >
            <template #icon>
                <IconPencil :size="14" stroke-width="1.5"/>
            </template>
        </Button>

        <Button
            type="button"
            severity="secondary"
            outlined
            size="small"
            class="rounded-full"
            @click="requireConfirmation('delete_category')"
        >
            <template #icon>
                <IconTrash :size="16" stroke-width="1.5" class="text-red-500"/>
            </template>
        </Button>
    </div>
</template>