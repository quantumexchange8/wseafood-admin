<script setup>
import ProductForm from "@/Pages/Product/Partials/ProductForm.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    groupCount: Number,
    product: Object,
});
</script>

<template>
    <AuthenticatedLayout :title="$t('public.edit_meal_item')">
        <div class="flex flex-col items-start gap-5 flex-1">
            <div class="flex flex-col items-start self-stretch">
                <h1 class="text-2xl font-bold">
                    {{ $t('public.edit_meal_item') }}
                </h1>
                <div class="self-stretch text-sm">
                    {{ $t('public.edit_meal_item_caption') }}
                </div>
            </div>
            <ProductForm
                :product="product"
                :groupCount="groupCount"
            />
        </div>
    </AuthenticatedLayout>
</template>
