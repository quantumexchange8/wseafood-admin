<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ModifierItemTable from '@/Pages/ModifierItem/Partials/ModifierItemTable.vue';
import { Button } from 'primevue';
import { IconPlus } from '@tabler/icons-vue';
import { generalFormat } from '@/Composables/format';
import CreateModifierItemModal from '@/Pages/ModifierGroup/Partials/CreateModifierItemModal.vue';
import { ref } from 'vue';

const props = defineProps({
    modifierItem: Object,
    itemCount: Number,
});

const { formatDateTime } = generalFormat();

const createModalVisible = ref(false);

</script>

<template>
    <AuthenticatedLayout :title="$t('public.modifier_item')" >
        <div class="flex flex-col items-center gap-4 self-stretch">
            <div class="flex items-center justify-center gap-4 self-stretch">
                <div class="flex flex-col items-start flex-1">
                    <div class="self-stretch text-2xl font-bold">
                        {{ $t('public.modifier_item') }}
                    </div>
                    <div class="self-stretch text-sm">
                        {{ $t('public.last_update_on') }}: {{ modifierItem?formatDateTime(modifierItem.updated_at):'' }}
                    </div>
                </div>
                <Button
                    type="button"
                    :label="$t('public.create_modifier_item')"
                    @click="createModalVisible = true"
                >
                    <template #icon>
                        <IconPlus :size="20"/>
                    </template>
                </Button>
            </div>
            <ModifierItemTable :categoryCount="itemCount" />
        </div>

        <CreateModifierItemModal
            :visible="createModalVisible"
            @update:visible="createModalVisible = $event"
        />
    </AuthenticatedLayout>
</template>
