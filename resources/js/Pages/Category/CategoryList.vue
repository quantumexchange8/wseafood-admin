<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from 'primevue';
import { IconPlus } from '@tabler/icons-vue';
import { ref } from "vue";

</script>

<template>
    <Head title="Category" />

    <AuthenticatedLayout title="Category">
        <div class="flex px-4 pt-2 pb-5 flex-col items-center gap-4 flex-1">
            <div class="flex items-center justify-center gap-4 self-stretch">
                <div class="flex flex-col items-start flex-1">
                    <div class="self-stretch text-2xl font-bold">
                        Category List
                    </div>
                    <div class="self-stretch text-sm">
                        Last update on
                    </div>
                </div>
                <Button
                    type="button"
                    label="Create Category"
                    :href="route('category.create')"
                >
                    <template #icon>
                        <IconPlus :size="20"/>
                    </template>
                </Button>
            </div>

            <div class="flex items-center gap-3 self-stretch">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-slate-400">search</span>
                    <span class="p-2 bg-slate-400">filter</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
