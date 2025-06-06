<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CategoryForm from '@/Pages/Category/Partials/CategoryForm.vue';
import { Button } from 'primevue';
import { Head } from '@inertiajs/vue3';
import { ref } from "vue";

const category_form = ref(null);

const submitForm = () => {
    if(category_form.value) {
        category_form.value.submitForm();
    } else {
        console.error("CategoryForm component is not available.");
    }
};

</script>

<template>
    <Head :title="$t('public.create_category')" />

    <AuthenticatedLayout :title="$t('public.create_category')">
        <div class="px-4 pt-2 pb-5 flex items-start gap-4">
            <div class="flex flex-col items-start gap-5 flex-1">
                <div class="flex flex-col items-start self-stretch">
                    <h1 class="text-2xl font-bold">
                        {{ $t('public.create_category') }}
                    </h1>
                    <div class="self-stretch text-sm">
                        {{ $t('public.create_category_caption') }}
                    </div>
                </div>
                <CategoryForm ref="category_form" />
            </div>
        </div>

        <div class="mx-4 mt-1.5 px-5 py-4 flex justify-between items-center border-t border-solid border-red-200 bg-white shadow-sm">
            <Button
                type="button"
                severity="secondary"
                outlined
                :label="$t('public.cancel')"
                @click="() => $inertia.visit(route('category.index'))"
            />
            <Button
                type="button"
                severity="primary"
                :label="$t('public.submit')"
                @click.prevent="submitForm"
            />
        </div>

    </AuthenticatedLayout>
</template>