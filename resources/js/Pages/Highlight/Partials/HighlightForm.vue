<script setup>
import { Card, Button, InputText, RadioButton, Avatar } from 'primevue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { IconUpload } from '@tabler/icons-vue';
import InputError from '@/Components/InputError.vue';
import IconAlertTooltip from '@/Components/IconAlertTooltip.vue';

const props = defineProps({
    highlight: Object,
});

const form = useForm({
    title: '',
    content: '',
    status: '',
    highlight_photo: null,
    photo_action:'',
    popup: '0',
});

const selectedHighlightPhoto = ref(null) //ref(props.category.category_thumbnail);
const handleUploadHighlightPhoto = (event) => {
    const highlightPhotoInput = event.target;
    const file = highlightPhotoInput.files[0];

    if (file) {
        // Display the selected image
        const reader = new FileReader();
        reader.onload = () => {
            selectedHighlightPhoto.value = reader.result;
        };
        reader.readAsDataURL(file);
        form.highlight_photo = event.target.files[0];
        form.photo_action = '';
    } else {
        selectedHighlightPhoto.value = null;
    }
};

const removeHighlightPhoto = () => {
    selectedHighlightPhoto.value = null;
    form.highlight_photo = null;
    form.photo_action = 'remove';
};

const submitForm = () => {
    form.post(route('highlight.store'), {
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
        },
    })
};

const availableLocales = JSON.parse(usePage().props.availableLocales);

</script>

<template>
    <form @submit.prevent="submitForm" class="flex flex-col items-start gap-4 self-stretch">
        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.highlight_detail') }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $t('public.highlight_detail_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="py-5 flex flex-col items-start gap-5 self-stretch">
                    <div class="px-5 flex items-center gap-5 self-stretch">
                        <div class="w-1/5 flex items-center gap-1">
                            <label for="name" class="text-sm">
                                {{ $t('public.title') }}
                            </label>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <InputText
                            v-model="form.title"
                            id="name"
                            class="w-1/3"
                            :placeholder="$t('public.popup_title_placeholder')"
                        />
                        <InputError :message="form.errors.title" />
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
                                <RadioButton v-model="form.status" inputId="display" value="active" />
                                <label for="display" class="text-sm">
                                    {{ $t('public.display') }}
                                </label>
                            </div>
                            <div class="flex items-center gap-3">
                                <RadioButton v-model="form.status" inputId="hidden" value="inactive" />
                                <label for="hidden" class="text-sm">
                                    {{ $t('public.hidden') }}
                                </label>
                            </div>
                        </div>
                        <InputError :message="form.errors.status" />
                    </div>

                    <div class="px-5 flex items-center gap-5 self-stretch">
                        <div class="w-1/5 flex items-center gap-1">
                            <div class="text-sm">
                                {{ $t('public.popup_highlight') }}
                            </div>
                            <IconAlertTooltip :message="$t('public.popup_highlight_tooltip')" />
                        </div>
                        <div class="flex items-center gap-5">
                            <div class="flex items-center gap-3">
                                <RadioButton v-model="form.popup" inputId="yes" value="1" />
                                <label for="yes" class="text-sm">
                                    {{ $t('public.yes') }}
                                </label>
                            </div>
                            <div class="flex items-center gap-3">
                                <RadioButton v-model="form.popup" inputId="no" value="0" />
                                <label for="no" class="text-sm">
                                    {{ $t('public.no') }}
                                </label>
                            </div>
                        </div>
                        <InputError :message="form.errors.popup" />
                    </div>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.content') }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $t('public.content_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <!-- tip tap editor -->
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.image') }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $t('public.image_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <input
                    ref="highlightPhotoInput"
                    id="highlight_photo_input"
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="handleUploadHighlightPhoto"
                />
                <div class="px-5 py-5 flex items-center gap-4">
                    <Button
                        v-if="!selectedHighlightPhoto"
                        type="button"
                        :label="$t('public.upload')"
                        @click.prevent="$refs.highlightPhotoInput.click()"
                    >
                        <template #icon>
                            <IconUpload :size="20"/>
                        </template>
                    </Button>
                    <div v-if="selectedHighlightPhoto" class="flex items-end gap-5 self-stretch">
                        <Avatar
                            v-if="selectedHighlightPhoto"
                            :image="selectedHighlightPhoto"
                            class="w-20 h-20"
                        />
                        <input
                            ref="highlightPhotoInput"
                            id="highlight_photo_input"
                            type="file"
                            class="hidden"
                            accept="image/*"
                            @change="handleUploadHighlightPhoto"
                        />
                        <div class="flex flex-col md:flex-row justify-end items-end gap-5 self-stretch">
                            <Button
                                type="button"
                                severity="secondary"
                                outlined
                                :label="$t('public.change')"
                                @click.prevent="$refs.highlightPhotoInput.click()"
                            />
                            <Button
                                type="button"
                                severity="danger"
                                outlined
                                :label="$t('public.remove')"
                                :disabled="!selectedHighlightPhoto || form.processing"
                                @click.prevent="removeHighlightPhoto"
                            />
                        </div>
                    </div>
                    <InputError :message="form.errors.highlight_photo" />
                </div>
            </template>
        </Card>

        <div class="w-full mt-1 px-5 py-4 flex justify-between items-center border-t border-solid border-primary-200 bg-white shadow-sm dark:bg-surface-900">
            <Button
                type="button"
                severity="secondary"
                outlined
                :label="$t('public.cancel')"
                @click="() => $inertia.visit(route('highlight.index'))"
            />
            <Button
                type="button"
                severity="primary"
                :label="$t('public.submit')"
                @click.prevent="submitForm"
                :disabled="form.processing"
            />
        </div>
    </form>
</template>