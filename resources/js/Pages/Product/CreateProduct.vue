<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductForm from '@/Pages/Product/Partials/ProductForm.vue';

const props = defineProps({
    groupCount: Number,
});
</script>

<template>
    <AuthenticatedLayout :title="$t('public.create_meal_item')">
        <div class="flex flex-col items-start gap-5 flex-1">
            <div class="flex flex-col items-start self-stretch">
                <h1 class="text-2xl font-bold">
                    {{ $t('public.create_meal_item') }}
                </h1>
                <div class="self-stretch text-sm">
                    {{ $t('public.create_meal_item_caption') }}
                </div>
            </div>
            <ProductForm :groupCount="groupCount" />
        </div>
    </AuthenticatedLayout>
</template>
