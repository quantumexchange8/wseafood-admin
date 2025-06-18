<script setup>
import { ConfirmDialog, useConfirm } from 'primevue';
import { ref } from 'vue';
import { IconTrash } from '@tabler/icons-vue';
import { trans } from 'laravel-vue-i18n';
import { router } from '@inertiajs/vue3';

const confirm = useConfirm();

const deleteItem = (itemId, itemName, path) => {
    const message = trans('public.delete_confirm_message')+ itemName;

    confirm.require({
        header: trans('public.delete_confirm_header'),
        message: message,
        rejectProps: {
            label: trans('public.cancel'),
            outlined: true,
            size: 'small'
        },
        acceptProps: {
            label: trans('public.delete'),
            size: 'small'
        },
        accept: () => {
            router.post(route(path, {'id': itemId}), {}, {
                preserveScroll: true,
                onSuccess: () => {}
            });
        },
    });
};

defineExpose({
    deleteItem,
});
</script>


<template>
    <ConfirmDialog>
        <template #message="slotProps">
            <div class="p-4 flex flex-col items-center w-full gap-4 border-b border-surface-200 dark:border-surface-700">
                <IconTrash :size="50" class="text-primary" />
        
                <p>{{ slotProps.message.message }}</p>
            </div>
        </template>
    </ConfirmDialog>
</template>