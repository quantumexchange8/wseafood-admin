<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MemberTable from '@/Pages/Member/Partials/MemberTable.vue';
import { generalFormat } from '@/Composables/format';

const props = defineProps({
    member: Object,
    memberCount: Number,
});

const { formatDateTime } = generalFormat();

</script>

<template>
    <AuthenticatedLayout :title="$t('public.member')" >
        <div class="flex flex-col items-center gap-4 self-stretch">
            <div class="flex items-center justify-center gap-4 self-stretch">
                <div class="flex flex-col items-start flex-1">
                    <div class="self-stretch text-2xl font-bold">
                        {{ $t('public.member') }}
                    </div>
                    <div class="self-stretch text-sm">
                        {{ $t('public.last_update_on') }}: {{ member?formatDateTime(member.updated_at):'' }}
                    </div>
                </div>
            </div>
            <MemberTable
                :memberCount="memberCount"
            />
        </div>
    </AuthenticatedLayout>
</template>
