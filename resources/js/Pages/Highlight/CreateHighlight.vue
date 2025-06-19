<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import HighlightForm from '@/Pages/Highlight/Partials/HighlightForm.vue';
import { ref } from "vue";

</script>

<template>
    <AuthenticatedLayout :title="$t('public.create_highlight')">
        <div class="px-4 pt-2 pb-5 flex items-start gap-4">
            <div class="flex flex-col items-start gap-5 flex-1">
                <div class="flex flex-col items-start self-stretch">
                    <h1 class="text-2xl font-bold">
                        {{ $t('public.create_highlight') }}
                    </h1>
                    <div class="self-stretch text-sm">
                        {{ $t('public.create_category_caption') }}
                    </div>
                </div>
                <HighlightForm />
            </div>
        </div>
    </AuthenticatedLayout>
</template>