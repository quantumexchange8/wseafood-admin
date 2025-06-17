<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ModifierGroupForm from '@/Pages/ModifierGroup/Partials/ModifierGroupForm.vue';

const props = defineProps({
    itemCount: Number,
});
</script>

<template>
    <AuthenticatedLayout :title="$t('public.create_modifier_group')">
        <div class="px-4 pt-2 pb-5 flex items-start gap-4">
            <div class="flex flex-col items-start gap-5 flex-1">
                <div class="flex flex-col items-start self-stretch">
                    <h1 class="text-2xl font-bold">
                        {{ $t('public.create_modifier_group') }}
                    </h1>
                    <div class="self-stretch text-sm">
                        {{ $t('public.create_modifier_group_caption') }}
                    </div>
                </div>
                <ModifierGroupForm :itemCount='itemCount' />
            </div>
        </div>

    </AuthenticatedLayout>
</template>