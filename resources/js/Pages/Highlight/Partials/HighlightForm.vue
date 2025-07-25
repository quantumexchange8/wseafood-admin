<script setup>
import { Card, Button, InputText, RadioButton, Avatar } from 'primevue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { IconUpload } from '@tabler/icons-vue';
import InputError from '@/Components/InputError.vue';
import IconAlertTooltip from '@/Components/IconAlertTooltip.vue';
import TipTapEditor from '@/Components/TipTapEditor.vue';

const props = defineProps({
    highlight: Object,
});

const form = useForm({
    id: props.highlight?.id ?? '',
    title: props.highlight?.title ?? '',
    content: props.highlight?.content ?? '',
    status: props.highlight?.status ?? '',
    highlight_photo: props.highlight?.highlight_photo ?? null,
    photo_action:'',
    popup: props.highlight?.can_popup ?? 0,
});

const selectedHighlightPhoto = ref(props.highlight?.highlight_photo ?? null);
const handleUploadHighlightPhoto = (event) => {
    const highlightPhotoInput = event.target;
    const file = highlightPhotoInput.files[0];

    if (file) {
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
    const path = ref('highlight.store');
    if(props.highlight) {
        path.value = 'highlight.update';
    }

    form.post(route(path.value, props.highlight?.id), {
        onSuccess: () => {
            form.reset();
            selectedHighlightPhoto.value = null;
            form.highlight_photo = null;
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
                        {{ $t('public.highlight_detail') }}
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
                        {{ $t('public.highlight_detail_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="p-5 grid grid-cols-4 items-center gap-5 self-stretch">
                    <div class="w-full flex items-center gap-1">
                        <label for="name" class="text-sm">
                            {{ $t('public.title') }}
                        </label>
                        <div class="text-xs text-red-500">
                            *
                        </div>
                    </div>
                    <div class="col-span-2 flex flex-col gap-1">
                        <InputText
                            v-model="form.title"
                            id="name"
                            class="w-full"
                            :placeholder="$t('public.popup_title_placeholder')"
                            :invalid="!!form.errors.title"
                        />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div></div>

                    <div class="w-1/5 flex items-center gap-1">
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
                            {{ $t('public.popup_highlight') }}
                        </div>
                        <IconAlertTooltip :message="$t('public.popup_highlight_tooltip')" />
                    </div>
                    <div class="col-span-2 flex flex-col gap-1">
                        <div class="flex items-center gap-5">
                            <div class="flex items-center gap-3">
                                <RadioButton 
                                    v-model="form.popup" 
                                    inputId="yes" 
                                    :value="1" 
                                    :invalid="!!form.errors.popup"
                                />
                                <label for="yes" class="text-sm">
                                    {{ $t('public.yes') }}
                                </label>
                            </div>
                            <div class="flex items-center gap-3">
                                <RadioButton 
                                    v-model="form.popup" 
                                    inputId="no" 
                                    :value="0" 
                                    :invalid="!!form.errors.popup"
                                />
                                <label for="no" class="text-sm">
                                    {{ $t('public.no') }}
                                </label>
                            </div>
                        </div>
                        <InputError :message="form.errors.popup" />
                    </div>
                    <div></div>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.content') }}
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
                        {{ $t('public.content_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <TipTapEditor 
                    v-model="form.content" 
                    :invalid="form.errors.content"
                />
                <InputError :message="form.errors.content" />
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.image') }}
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
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