<script setup>
import { ConfirmDialog, useConfirm } from 'primevue';
import { ref } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { router } from '@inertiajs/vue3';

const confirm = useConfirm();

const message = ref();

const changeStatus = (itemId, itemName, itemStatus, path) => {  
    if(itemStatus === 'active') {
        message.value = trans('public.change_status_message_hide')+ itemName;
    } else {
        message.value = trans('public.change_status_message_show')+ itemName;
    }

    confirm.require({
        header: trans('public.change_status_header'),
        message: message.value,
        rejectProps: {
            label: trans('public.cancel'),
            outlined: true,
            size: 'small'
        },
        acceptProps: {
            label: trans('public.save'),
            size: 'small'
        },
        accept: () => {
            router.post(route(path), {
                'id': itemId,
                'name': itemName,
                'status': itemStatus,
            }, {
                preserveScroll: true,
                onSuccess: () => {}
            });
        },
    });
};

const deleteItem = (itemId, itemName, path) => {
    message.value = trans('public.delete_confirm_message')+ itemName;

    confirm.require({
        header: trans('public.delete_confirm_header'),
        message: message.value,
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
            router.post(route(path), {'id': itemId}, {
                preserveScroll: true,
                onSuccess: () => {}
            });
        },
    });
};

defineExpose({
    deleteItem, changeStatus,
});
</script>


<template>
    <ConfirmDialog>
        <template #message="slotProps">
            <div class="p-5 flex flex-col items-center w-full gap-4 border-b border-surface-200 dark:border-surface-700">
                <p>{{ slotProps.message.message }}</p>
            </div>
        </template>
    </ConfirmDialog>
</template>