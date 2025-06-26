<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import NotificationTable from '@/Pages/PushNotification/Partials/NotificationTable.vue';
import { Button } from 'primevue';
import { IconPlus } from '@tabler/icons-vue';
import { generalFormat } from '@/Composables/format';

const props = defineProps({
    notification: Object,
    notificationCount: Number,
});

const { formatDateTime } = generalFormat();

</script>

<template>
    <AuthenticatedLayout :title="$t('public.push_notification')" >
        <div class="flex flex-col items-center gap-4 self-stretch">
            <div class="flex items-center justify-center gap-4 self-stretch">
                <div class="flex flex-col items-start flex-1">
                    <div class="self-stretch text-2xl font-bold dark:text-white">
                        {{ $t('public.push_notification_list') }}
                    </div>
                    <div class="self-stretch text-sm text-surface-500">
                        {{ $t('public.last_update_on') }}: {{ notification?formatDateTime(notification.updated_at):'' }}
                    </div>
                </div>
                <Button
                    type="button"
                    :label="$t('public.create_notification')"
                    @click="() => $inertia.visit(route('notification.create'))"
                >
                    <template #icon>
                        <IconPlus :size="20"/>
                    </template>
                </Button>
            </div>
            <NotificationTable :notificationCount="notificationCount" />
        </div>
    </AuthenticatedLayout>
</template>
