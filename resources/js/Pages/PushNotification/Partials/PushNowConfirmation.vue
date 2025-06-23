<script setup>
import { ConfirmDialog, useConfirm } from 'primevue';
import { ref } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { router } from '@inertiajs/vue3';

const confirm = useConfirm();

const pushStatus = (itemId = null, itemName = '', path = null) => {  
    confirm.require({
        header: trans('public.push_notification_header'),
        message: trans('public.push_notification_message')+ itemName??'',
        rejectProps: {
            label: trans('public.no'),
            outlined: true,
            size: 'small'
        },
        acceptProps: {
            label: trans('public.yes'),
            size: 'small'
        },
        accept: () => {
            if(itemId) {
                // post push notification
            } else {
                emit('update:pushNow', true);
            }
        },
        reject: () => {
            if(itemId) {
                // post push notification
            } else {
                emit('update:pushNow', false);
            }
        },
    });
};

const emit = defineEmits([
    'update:pushNow',
]);

defineExpose({
    pushStatus,
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