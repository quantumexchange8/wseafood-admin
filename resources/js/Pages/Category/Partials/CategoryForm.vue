<script setup>
import { Card, Button, InputText, RadioButton, Avatar } from 'primevue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {generalFormat} from "@/Composables/format.js";

const props = defineProps({
    category: Object,
});

const { formatNameLabel } = generalFormat();

const form = useForm({
    name: '',
    status: '',
    category_thumbnail: null,
    photo_action:'',
});

const selectedCategoryPhoto = ref(null) //ref(props.category.category_thumbnail);
const handleUploadCategoryPhoto = (event) => {
    const categoryPhotoInput = event.target;
    const file = categoryPhotoInput.files[0];

    if (file) {
        // Display the selected image
        const reader = new FileReader();
        reader.onload = () => {
            selectedCategoryPhoto.value = reader.result;
        };
        reader.readAsDataURL(file);
        form.category_thumbnail = event.target.files[0];
        form.photo_action = '';
    } else {
        selectedCategoryPhoto.value = null;
    }
};

const removeCategoryPhoto = () => {
    selectedCategoryPhoto.value = null;
    form.category_thumbnail = null;
    form.photo_action = 'remove';
};

const submitForm = () => {
    emit('formSubmitted', true);

    form.post(route('category.store'), {
        onSuccess: () => {
            form.reset();
            emit('formSubmitted', false);
        },
        onError: () => {
            emit('formSubmitted', false);
        },
    })
};

const emit = defineEmits(['formSubmitted']);

defineExpose({
    submitForm
});
</script>

<template>
    <form @submit.prevent="submitForm" class="flex flex-col items-start gap-4 self-stretch">
        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.category_detail') }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $t('public.category_detail_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="py-5 flex flex-col items-start gap-5 self-stretch">
                    <div class="px-5 flex items-center gap-5 self-stretch">
                        <div class="w-1/5 flex items-center gap-1">
                            <label for="category-name" class="text-sm">
                                {{ $t('public.category_name') }}
                            </label>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <InputText
                            v-model="form.name"
                            id="category-name"
                            class="w-1/3"
                            placeholder="e.g. BBQ Series"
                        />
                    </div>

                    <div class="px-5 flex items-center gap-5 self-stretch">
                        <div class="w-1/5 flex items-center gap-1">
                            <div class="text-sm">
                                {{ $t('public.visibility') }}
                            </div>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <div class="flex items-center gap-5">
                            <div class="flex items-center gap-3">
                                <RadioButton v-model="form.status" inputId="display" value="1" />
                                <label for="display" class="text-sm">
                                    {{ $t('public.display') }}
                                </label>
                            </div>
                            <div class="flex items-center gap-3">
                                <RadioButton v-model="form.status" inputId="hidden" value="0" />
                                <label for="hidden" class="text-sm">
                                    {{ $t('public.hidden') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 flex items-center gap-5 self-stretch">
                        <div class="w-1/5 flex items-center gap-1">
                            <div class="text-sm">
                                {{ $t('public.category_thumbnail') }}
                            </div>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <div class="flex items-end gap-5 self-stretch">
                            <Avatar
                                v-if="selectedCategoryPhoto"
                                :image="selectedCategoryPhoto"
                                class="w-20 h-20"
                            />
                            <Avatar
                                v-else
                                :label="formatNameLabel(form.name)"
                                class="w-20 h-20"
                                size="large"
                            />
                            <input
                                ref="categoryPhotoInput"
                                id="category_photo_input"
                                type="file"
                                class="hidden"
                                accept="image/*"
                                @change="handleUploadCategoryPhoto"
                            />
                            <div class="flex flex-col md:flex-row justify-end items-end gap-5 self-stretch">
                                <Button
                                    type="button"
                                    severity="secondary"
                                    outlined
                                    :label="$t('public.change')"
                                    @click.prevent="$refs.categoryPhotoInput.click()"
                                />
                                <Button
                                    type="button"
                                    severity="danger"
                                    outlined
                                    :label="$t('public.remove')"
                                    :disabled="!selectedCategoryPhoto || form.processing"
                                    @click.prevent="removeCategoryPhoto"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </form>
</template>