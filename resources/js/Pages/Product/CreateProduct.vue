<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductForm from '@/Pages/Product/Partials/ProductForm.vue';
import { Button } from 'primevue';
import { ref } from "vue";

const product_form = ref(null);
const disableBtn = ref(false);

const submitForm = () => {
    if(product_form.value) {
        product_form.value.submitForm();
    } else {
        console.error("ProductForm component is not available.");
    }
};

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
                <ProductForm ref="product_form" @formSubmitted="disableBtn = $event" />
            </div>
        </div>

        <div class="mx-4 mt-1.5 px-5 py-4 flex justify-between items-center border-t border-solid border-primary-200 bg-white shadow-sm">
            <Button
                type="button"
                severity="secondary"
                outlined
                :label="$t('public.cancel')"
                @click="() => $inertia.visit(route('product.index'))"
            />
            <Button
                type="button"
                severity="primary"
                :label="$t('public.submit')"
                @click.prevent="submitForm"
                :disabled="disableBtn"
            />
        </div>

    </AuthenticatedLayout>
</template>