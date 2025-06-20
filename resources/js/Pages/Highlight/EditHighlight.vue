<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import HighlightForm from '@/Pages/Highlight/Partials/HighlightForm.vue';

const props = defineProps({
    highlight: Object,
});
</script>

<template>
    <AuthenticatedLayout :title="$t('public.edit_highlight')">
        <div class="px-4 pt-2 pb-5 flex items-start gap-4">
            <div class="flex flex-col items-start gap-5 flex-1">
                <div class="flex flex-col items-start self-stretch">
                    <h1 class="text-2xl font-bold">
                        {{ $t('public.edit_highlight') }}
                    </h1>
                    <div class="self-stretch text-sm">
                        {{ $t('public.edit_highlight_caption') }}
                    </div>
                </div>
                <HighlightForm :highlight="highlight"/>
            </div>
        </div>
    </AuthenticatedLayout>
</template>