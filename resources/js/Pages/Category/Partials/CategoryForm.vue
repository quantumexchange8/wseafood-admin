<script setup>
import { Card, Button, InputText, RadioButton, Avatar } from 'primevue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import {generalFormat} from "@/Composables/format.js";
import InputError from '@/Components/InputError.vue';
import {useLangObserver} from "@/Composables/localeObserver.js";
import TipTapEditor from '@/Components/TipTapEditor.vue';

const props = defineProps({
    category: Object,
});

const { formatNameLabel } = generalFormat();
const {locale} = useLangObserver();

const availableLocales = JSON.parse(usePage().props.availableLocales);

const form = useForm({
    id: props.category?.id ?? '',
    prefix: props.category?.prefix ?? '',
    name: props.category?.name ?? {},
    status: props.category?.status ?? '',
    category_thumbnail: props.category?.category_thumbnail ?? null,
    photo_action:'',
    description: props.category?.description ?? '',
});

const selectedCategoryPhoto = ref(props.category?.category_thumbnail ?? null);
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
    const path = ref('category.store');
    if(props.category) {
        path.value = 'category.update';
    }

    form.post(route(path.value, props.category?.id), {
        onSuccess: () => {
            form.reset();
            selectedCategoryPhoto.value = null;
            form.category_thumbnail = null;
        },
        onError: () => {
        },
    })
};

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
                <div class="p-5 grid grid-cols-4 items-center gap-5 self-stretch">
                    <div class="w-full flex items-center gap-1">
                        <label for="category-prefix" class="text-sm">
                            {{ $t('public.category_prefix') }}
                        </label>
                        <div class="text-xs text-red-500">
                            *
                        </div>
                    </div>
                    <div class="col-span-2 flex flex-col gap-1">
                        <InputText
                            v-model="form.prefix"
                            id="category-prefix"
                            class="w-full"
                            :placeholder="$t('public.category_prefix_placeholder')"
                            :invalid="!!form.errors.prefix"
                        />
                        <InputError :message="form.errors.prefix" />
                    </div>
                    <div></div>

                    <template
                        v-for="lang in availableLocales"
                        :key="lang"
                    >
                        <div class="w-full flex items-center gap-1">
                            <label :for="`category-name-${lang.value}`" class="text-sm">
                                {{ $t('public.category_name') }} ({{ lang.label }})
                            </label>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <div class="col-span-2 flex flex-col gap-1">
                            <InputText
                                v-model="form.name[lang.value]"
                                :id="`category-name-${lang.value}`"
                                class="w-full"
                                :placeholder="$t('public.category_name_placeholder')"
                                :invalid="!!form.errors[`name.${lang.value}`]"
                            />
                            <InputError :message="form.errors[`name.${lang.value}`]" />
                        </div>
                        <div></div>
                    </template>

                    <div class="w-full flex items-center gap-1">
                        <div class="text-sm">
                            {{ $t('public.visibility') }}
                        </div>
                        <div class="text-xs text-red-500">
                            *
                        </div>
                    </div>
                    <div class="col-span-2 flex flex-col gap-1">
                        <div class="flex items-center gap-5">
                            <div class="flex items-center gap-3">
                                <RadioButton
                                    v-model="form.status"
                                    inputId="display"
                                    value="active"
                                    :invalid="!!form.errors.status"
                                />
                                <label for="display" class="text-sm">
                                    {{ $t('public.display') }}
                                </label>
                            </div>
                            <div class="flex items-center gap-3">
                                <RadioButton
                                    v-model="form.status"
                                    inputId="hidden"
                                    value="inactive"
                                    :invalid="!!form.errors.status"
                                />
                                <label for="hidden" class="text-sm">
                                    {{ $t('public.hidden') }}
                                </label>
                            </div>
                        </div>
                        <InputError :message="form.errors.status" />
                    </div>
                    <div></div>

                    <div class="w-full flex items-center gap-1">
                        <div class="text-sm">
                            {{ $t('public.category_thumbnail') }}
                        </div>
                    </div>
                    <div class="col-span-2 flex flex-col gap-1">
                        <div class="flex items-end gap-5 self-stretch">
                            <Avatar
                                v-if="selectedCategoryPhoto"
                                :image="selectedCategoryPhoto"
                                class="w-20 h-20"
                            />
                            <Avatar
                                v-else
                                :label="formatNameLabel(form.name[locale])"
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
                        <InputError :message="form.errors.category_thumbnail" />
                    </div>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.description') }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $t('public.description_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <TipTapEditor 
                    v-model="form.description" 
                    :invalid="form.errors.description"
                />
                <InputError :message="form.errors.description" />
            </template>
        </Card>

        <div class="w-full mt-1 px-5 py-4 flex justify-between items-center border-t border-solid border-primary-200 bg-white shadow-sm dark:bg-surface-900">
            <Button
                type="button"
                severity="secondary"
                outlined
                :label="$t('public.cancel')"
                @click="() => $inertia.visit(route('category.index'))"
                :disabled="form.processing"
            />
            <Button
                type="submit"
                severity="primary"
                :label="$t('public.submit')"
                :disabled="form.processing"
            />
        </div>
    </form>
</template>