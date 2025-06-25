<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from 'primevue';
import { IconPlus } from '@tabler/icons-vue';
import { generalFormat } from '@/Composables/format';
import ModifierGroupTable from '@/Pages/ModifierGroup/Partials/ModifierGroupTable.vue';

const props = defineProps({
    modifierGroup:Object,
    groupCount: Number,
});

const { formatDateTime } = generalFormat();
</script>

<template>
    <AuthenticatedLayout :title="$t('public.modifier_group_list')" >
        <div class="flex px-4 pt-2 pb-5 flex-col items-center gap-4 flex-1">
            <div class="flex items-center justify-center gap-4 self-stretch">
                <div class="flex flex-col items-start flex-1">
                    <div class="self-stretch text-2xl font-bold">
                        {{ $t('public.modifier_group_list') }}
                    </div>
                    <div class="self-stretch text-sm">
                        {{ $t('public.last_update_on') }}: {{ modifierGroup?formatDateTime(modifierGroup.updated_at):'' }}
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Button
                        type="button"
                        :label="$t('public.create_modifier_group')"
                        @click="() => $inertia.visit(route('modifier.group.create'))"
                    >
                        <template #icon>
                            <IconPlus :size="20"/>
                        </template>
                    </Button>
                </div>
            </div>
            <ModifierGroupTable :groupCount="groupCount" />
        </div>
    </AuthenticatedLayout>
</template>
