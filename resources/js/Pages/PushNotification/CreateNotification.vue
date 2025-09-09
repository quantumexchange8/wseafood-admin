<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import NotificationForm from '@/Pages/PushNotification/Partials/NotificationForm.vue';

</script>

<template>
    <AuthenticatedLayout :title="$t('public.create_notification')">
        <div class="flex flex-col items-start gap-5 flex-1">
            <div class="flex flex-col items-start self-stretch">
                <h1 class="text-2xl font-bold">
                    {{ $t('public.create_notification') }}
                </h1>
                <div class="self-stretch text-sm">
                    {{ $t('public.create_notification_caption') }}
                </div>
            </div>
            <NotificationForm />
        </div>
    </AuthenticatedLayout>
</template>
