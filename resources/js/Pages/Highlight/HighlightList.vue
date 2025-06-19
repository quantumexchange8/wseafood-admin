<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import HighlightTable from '@/Pages/Highlight/Partials/HighlightTable.vue';
import { Button } from 'primevue';
import { IconPlus } from '@tabler/icons-vue';
import { generalFormat } from '@/Composables/format';

const props = defineProps({
    highlight: Object,
    highlightCount: Number,
});

const { formatDateTime } = generalFormat();

</script>

<template>
    <AuthenticatedLayout :title="$t('public.highlight')" >
        <div class="flex px-4 pt-2 pb-5 flex-col items-center gap-4 flex-1">
            <div class="flex items-center justify-center gap-4 self-stretch">
                <div class="flex flex-col items-start flex-1">
                    <div class="self-stretch text-2xl font-bold">
                        {{ $t('public.highlight_list') }}
                    </div>
                    <div class="self-stretch text-sm">
                        {{ $t('public.last_update_on') }}: {{ highlight?formatDateTime(highlight.updated_at):'' }}
                    </div>
                </div>
                <Button
                    type="button"
                    :label="$t('public.create_highlight')"
                    @click="() => $inertia.visit(route('highlight.create'))"
                >
                    <template #icon>
                        <IconPlus :size="20"/>
                    </template>
                </Button>
            </div>
            <HighlightTable :highlightCount="highlightCount" />
        </div>
    </AuthenticatedLayout>
</template>
