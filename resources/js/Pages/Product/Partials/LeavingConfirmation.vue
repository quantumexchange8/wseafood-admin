<script setup>
import { ConfirmDialog, useConfirm } from 'primevue';
import { trans } from 'laravel-vue-i18n';
import { router } from '@inertiajs/vue3';

const confirm = useConfirm();

const leavingPage = () => {
    confirm.require({
        header: trans('public.leave_confirm_title'),
        rejectProps: {
            label: trans('public.stay'),
            outlined: true,
            size: 'small'
        },
        acceptProps: {
            label: trans('public.leave'),
            size: 'small'
        },
        accept: () => {
            emit('update:leave', true);
            router.visit(route('modifier.group.create'));
        },
    });
};

const emit = defineEmits([
    'update:leave',
]);

defineExpose({
    leavingPage
});
</script>


<template>
    <ConfirmDialog>
        <template #message="slotProps">
            <div class="p-5 flex flex-col items-start w-full gap-5 border-b border-surface-200 dark:border-surface-700">
                <p>
                    {{ $t('public.leave_confirm_message') }}
                </p>
            </div>
        </template>
    </ConfirmDialog>
</template>