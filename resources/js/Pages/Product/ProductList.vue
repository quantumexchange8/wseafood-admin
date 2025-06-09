<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from 'primevue';
import { IconPlus } from '@tabler/icons-vue';
import { generalFormat } from '@/Composables/format';

const props = defineProps({
    product: Object,
    all: Object,
});

const { formatDateTime } = generalFormat();
</script>

<template>
    <AuthenticatedLayout :title="$t('public.product_list')" >
        <div class="flex px-4 pt-2 pb-5 flex-col items-center gap-4 flex-1">
            <div class="flex items-center justify-center gap-4 self-stretch">
                <div class="flex flex-col items-start flex-1">
                    <div class="self-stretch text-2xl font-bold">
                        {{ $t('public.product_list') }}
                    </div>
                    <div class="self-stretch text-sm">
                        {{ $t('public.last_update_on') }}: {{ product?formatDateTime(product.updated_at):'' }}
                    </div>
                </div>
                <Button
                    type="button"
                    :label="$t('public.create_product')"
                    @click="() => $inertia.visit(route('product.create'))"
                >
                    <template #icon>
                        <IconPlus :size="20"/>
                    </template>
                </Button>
            </div>
            
        </div>
    </AuthenticatedLayout>
</template>
