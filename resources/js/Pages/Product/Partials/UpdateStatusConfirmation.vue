<script setup>
import { ConfirmDialog } from 'primevue'
import { useConfirm } from "primevue/useconfirm";
import { ref } from 'vue';
import { IconAlertCircle } from '@tabler/icons-vue'
import {useLangObserver} from "@/Composables/localeObserver.js";
import { trans } from 'laravel-vue-i18n';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    item: String,
})

const confirm = useConfirm();
const { locale } = useLangObserver();

const message = ref();
const updateItem = ref(null);

const changeStatus = (item) => {
    updateItem.value = item;    
    const itemName = JSON.parse(item.name)[locale.value] ?? JSON.parse(item.name)['en'];

    if(item.status === 'active') {
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
            const path = ref(null);
            if(props.item === 'product') {
                path.value = 'product.updateStatus';
            }
            else if(props.item === 'category') {
                path.value = 'category.updateStatus';
            }

            router.post(route(path.value, item), {}, {
                preserveScroll: true,
                onSuccess: () => {}
            });
        },
    });
};

defineExpose({
    changeStatus,
});
</script>

<template>
    <ConfirmDialog>
        <template #message="slotProps">
            <div class="p-4 flex flex-col items-center w-full gap-4 border-b border-surface-200 dark:border-surface-700">
                <IconAlertCircle :size="50" class="text-primary" />

                <p>{{ slotProps.message.message }}</p>
            </div>
        </template>
    </ConfirmDialog>
</template>