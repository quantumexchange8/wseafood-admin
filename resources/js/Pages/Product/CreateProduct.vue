<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductForm from '@/Pages/Product/Partials/ProductForm.vue';

</script>

<template>
    <AuthenticatedLayout :title="$t('public.create_product')">
        <div class="px-4 pt-2 pb-5 flex items-start gap-4">
            <div class="flex flex-col items-start gap-5 flex-1">
                <div class="flex flex-col items-start self-stretch">
                    <h1 class="text-2xl font-bold">
                        {{ $t('public.create_meal_item') }}
                    </h1>
                    <div class="self-stretch text-sm">
                        {{ $t('public.create_meal_item_caption') }}
                    </div>
                </div>
                <ProductForm />
            </div>
        </div>
    </AuthenticatedLayout>
</template>