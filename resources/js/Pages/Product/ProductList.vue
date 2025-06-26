<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from 'primevue';
import { IconPlus, IconUpload } from '@tabler/icons-vue';
import { generalFormat } from '@/Composables/format';
import ProductView from '@/Pages/Product/Partials/ProductView.vue';

const props = defineProps({
    product: Object,
    categories: Object,
    productCount: Number,
});

const { formatDateTime } = generalFormat();
</script>

<template>
    <AuthenticatedLayout :title="$t('public.product_list')" >
        <div class="flex flex-col items-center gap-4 self-stretch">
            <div class="flex items-center justify-center gap-4 self-stretch">
                <div class="flex flex-col items-start flex-1">
                    <div class="self-stretch text-2xl font-bold">
                        {{ $t('public.product_list') }}
                    </div>
                    <div class="self-stretch text-sm">
                        {{ $t('public.last_update_on') }}: {{ product?formatDateTime(product.updated_at):'' }}
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Button
                        type="button"
                        severity="secondary"
                        outlined
                    >
                        <IconUpload
                            :size="16"
                            stroke-width="1.5"
                        />
                        <span class="text-sm font-bold">
                            {{ $t('public.export') }}
                        </span>
                    </Button>
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

            <ProductView
                :categories="categories"
                :productCount="productCount"
            />
        </div>
    </AuthenticatedLayout>
</template>
