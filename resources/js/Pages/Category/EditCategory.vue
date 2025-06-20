<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CategoryForm from '@/Pages/Category/Partials/CategoryForm.vue';

const props = defineProps({
    category: Object,
});

</script>

<template>
    <AuthenticatedLayout :title="$t('public.edit_category')">
        <div class="px-4 pt-2 pb-5 flex items-start gap-4">
            <div class="flex flex-col items-start gap-5 flex-1">
                <div class="flex flex-col items-start self-stretch">
                    <h1 class="text-2xl font-bold">
                        {{ $t('public.edit_category') }}
                    </h1>
                    <div class="self-stretch text-sm">
                        {{ $t('public.edit_category_caption') }}
                    </div>
                </div>
                <CategoryForm :category="category" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>