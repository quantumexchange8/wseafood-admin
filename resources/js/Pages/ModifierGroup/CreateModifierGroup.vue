<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ModifierGroupForm from '@/Pages/ModifierGroup/Partials/ModifierGroupForm.vue';

const props = defineProps({
    itemCount: Number,
    categoryCount: Number,
});
</script>

<template>
    <AuthenticatedLayout :title="$t('public.create_modifier_group')">
        <div class="flex flex-col items-start gap-5 flex-1">
            <div class="flex flex-col items-start self-stretch">
                <h1 class="text-2xl font-bold">
                    {{ $t('public.create_modifier_group') }}
                </h1>
                <div class="self-stretch text-sm">
                    {{ $t('public.create_modifier_group_caption') }}
                </div>
            </div>
            <ModifierGroupForm
                :itemCount='itemCount'
                :categoryCount="categoryCount"
            />
        </div>
    </AuthenticatedLayout>
</template>
