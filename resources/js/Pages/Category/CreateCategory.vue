<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CategoryForm from '@/Pages/Category/Partials/CategoryForm.vue';

</script>

<template>
    <AuthenticatedLayout :title="$t('public.create_category')">
        <div class="flex flex-col items-start gap-5 flex-1">
            <div class="flex flex-col items-start self-stretch">
                <h1 class="text-2xl font-bold">
                    {{ $t('public.create_category') }}
                </h1>
                <div class="self-stretch text-sm">
                    {{ $t('public.create_category_caption') }}
                </div>
            </div>
            <CategoryForm />
        </div>
    </AuthenticatedLayout>
</template>
