<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { Dialog, InputText, Button, Avatar, RadioButton } from 'primevue';
import { ref, watch } from 'vue';
import {generalFormat} from "@/Composables/format.js";
import {useLangObserver} from "@/Composables/localeObserver.js";
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    visible: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['update:visible']);

const { formatNameLabel } = generalFormat();
const { locale } = useLangObserver();

const availableLocales = JSON.parse(usePage().props.availableLocales);

const modalVisible = ref(props.visible);

watch(() => props.visible, (val) => {
    modalVisible.value = val;
});

watch(modalVisible, (val) => {
    if (val !== props.visible) {
        emit('update:visible', val);
    }
});

const form = useForm({
    name: {},
    status: '',
    category_thumbnail: null,
    photo_action: ''
});

const submitForm = () => {
    form.post(route('category.store'), {
        onSuccess: () => {
            form.reset();
            selectedCategoryPhoto.value = null;
            form.category_thumbnail = null;
            modalVisible.value = false;
        },
    })
};

const selectedCategoryPhoto = ref(null);
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

</script>

<template>
    <Dialog
        v-model:visible="modalVisible"
        modal
        class="dialog-lg"
    >
        <template #header>
            <div class="flex items-center self-stretch gap-5">
                <span class="text-lg font-bold">
                    {{ $t('public.create_new_category') }}
                </span>
            </div>
        </template>

        <form @submit.prevent="submitForm" class="p-5 flex items-start flex-col gap-6">
            <div class="w-full grid grid-cols-2 gap-6">
                <div
                    v-for="lang in availableLocales"
                    :key="lang"
                    class="flex flex-col items-start gap-3"
                >
                    <label :for="`category-name-${lang.value}`" class="font-bold">
                        {{ $t('public.category_name') }} ({{ lang.label }})
                    </label>
                    <InputText
                        v-model="form.name[lang.value]"
                        :id="`category-name-${lang.value}`"
                        class="w-full"
                        :placeholder="$t('public.category_name_placeholder')"
                    />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="flex flex-col items-start gap-3">
                    <div class="font-bold">
                        {{ $t('public.visibility') }}
                    </div>
                    <div class="flex items-center gap-5">
                        <div class="flex items-center gap-3">
                            <RadioButton v-model="form.status" inputId="status-display" value="1" />
                            <label for="status-display" class="text-sm">
                                {{ $t('public.display') }}
                            </label>
                        </div>
                        <div class="flex items-center gap-3">
                            <RadioButton v-model="form.status" inputId="status-hidden" value="0" />
                            <label for="status-hidden" class="text-sm">
                                {{ $t('public.hidden') }}
                            </label>
                        </div>
                    </div>
                    <InputError :message="form.errors.status" />
                </div>
                <div class="flex flex-col items-start gap-3">
                    <div class="flex items-center gap-1">
                        <div class="font-bold">
                            {{ $t('public.category_thumbnail') }}
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
        </form>

        <template #footer>
            <Button
                type="button"
                label="Cancel"
                severity="secondary"
                outlined
                @click="modalVisible = false"
                :disabled="form.processing"
            />
            <Button
                type="submit"
                label="Create"
                @click="submitForm"
                :disabled="form.processing"
            />
        </template>
    </Dialog>
</template>