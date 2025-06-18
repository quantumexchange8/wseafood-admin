<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CategoryTable from '@/Pages/Category/Partials/CategoryTable.vue';
import { Button } from 'primevue';
import { IconPlus } from '@tabler/icons-vue';
import { generalFormat } from '@/Composables/format';

const props = defineProps({
    category: Object,
    categoryCount: Number,
});

const { formatDateTime } = generalFormat();

</script>

<template>
    <AuthenticatedLayout :title="$t('public.category')" >
        <div class="flex px-4 pt-2 pb-5 flex-col items-center gap-4 flex-1">
            <div class="flex items-center justify-center gap-4 self-stretch">
                <div class="flex flex-col items-start flex-1">
                    <div class="self-stretch text-2xl font-bold">
                        {{ $t('public.category_list') }}
                    </div>
                    <div class="self-stretch text-sm">
                        {{ $t('public.last_update_on') }}: {{ category?formatDateTime(category.updated_at):'' }}
                    </div>
                </div>
                <Button
                    type="button"
                    :label="$t('public.create_category')"
                    @click="() => $inertia.visit(route('category.create'))"
                >
                    <template #icon>
                        <IconPlus :size="20"/>
                    </template>
                </Button>
            </div>

            <CategoryTable :category="category" :categoryCount="categoryCount"/>
        </div>
    </AuthenticatedLayout>
</template>
