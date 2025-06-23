<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import NotificationForm from '@/Pages/PushNotification/Partials/NotificationForm.vue';

const props = defineProps({
    notification: Object,
});

</script>

<template>
    <AuthenticatedLayout :title="$t('public.edit_notification')">
        <div class="px-4 pt-2 pb-5 flex items-start gap-4">
            <div class="flex flex-col items-start gap-5 flex-1">
                <div class="flex flex-col items-start self-stretch">
                    <h1 class="text-2xl font-bold">
                        {{ $t('public.edit_notification') }}
                    </h1>
                    <div class="self-stretch text-sm">
                        {{ $t('public.edit_notification_caption') }}
                    </div>
                </div>
                <NotificationForm :notification="notification" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>