<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button, Card, InputText } from 'primevue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from "vue";

const form = useForm({
    name: '',
});

</script>

<template>
    <Head title="Create Category" />

    <AuthenticatedLayout title="Create Category">
        <div class="flex flex-col items-start gap-5 flex-1">
            <div class="flex flex-col items-start self-stretch">
                <h1 class="text-2xl font-bold">
                    Create Category
                </h1>
                <div class="self-stretch text-sm">
                    Add a new category for organising your menu item.
                </div>
            </div>

            <div class="flex flex-col items-start gap-4 self-stretch">
                <Card class="w-full">
                    <template #title>
                        Category detail
                    </template>
                    <template #content>
                        <div class="py-5 flex flex-col items-start gap-5 self-stretch">
                            <div class="px-5 flex items-center gap-5 self-stretch">
                                <div class="flex items-center gap-1">
                                    <div class="text-sm">
                                        Category Name
                                    </div>
                                    <div class="text-xs text-red-500">
                                        *
                                    </div>
                                </div>
                                <InputText
                                    v-model="form.name"
                                    class="w-full"
                                    placeholder="Enter category name"
                                    :aria-label="'Category Name'"
                                />
                            </div>

                            <div class="px-5 flex items-center gap-5 self-stretch">
                                <div class="flex items-center gap-1">
                                    <div class="text-sm">
                                        Visibility
                                    </div>
                                    <div class="text-xs text-red-500">
                                        *
                                    </div>
                                </div>
                                <!-- radio button -->
                            </div>

                            <div class="px-5 flex items-center gap-5 self-stretch">
                                <div class="flex items-center gap-1">
                                    <div class="text-sm">
                                        Category Thumbnail
                                    </div>
                                    <div class="text-xs text-red-500">
                                        *
                                    </div>
                                </div>
                                <!-- upload image -->
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>