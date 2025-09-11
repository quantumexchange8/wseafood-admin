<script setup>
import {Card, Image, Button, Avatar} from "primevue";
import FormLabel from "@/Components/FormLabel.vue";
import InputError from "@/Components/InputError.vue";
import {ref} from "vue";
import TipTapEditor from "@/Components/TipTapEditor.vue";

const props = defineProps({
    modelValue: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) },
})
const emit = defineEmits(["update:modelValue"]);

const selectedVoucherImage = ref(null);

const handleUpload = (e) => {
    const file = e.target.files[0]
    if (file) {
        const reader = new FileReader()
        reader.onload = () => {
            selectedVoucherImage.value = reader.result

            emit("update:modelValue", {
                ...props.modelValue,
                voucher_thumbnail: file,
                voucher_thumbnail_preview: reader.result,
            })
        }
        reader.readAsDataURL(file)
    }
}

const removePhoto = () => {
    selectedVoucherImage.value = null
    emit("update:modelValue", {
        ...props.modelValue,
        voucher_thumbnail: null,
        voucher_thumbnail_preview: null,
    })
}
</script>

<template>
    <div class="flex flex-col gap-4">
        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold flex gap-1 items-start">
                        {{ $t('public.image') }} <span class="text-red-500 text-xs pt-1">*</span>
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
                        {{ $t('public.voucher_image_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-5 self-stretch p-5">
                    <FormLabel
                        class="min-w-40"
                        :label="$t('public.select_an_image')"
                        for="voucher_image_input"
                    />

                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <div class="flex items-end gap-5 self-stretch">
                            <Image
                                v-if="selectedVoucherImage"
                                :src="selectedVoucherImage"
                                preview
                                class="rounded-md bg-surface-200 dark:bg-surface-700"
                                image-class="w-20 h-20 rounded-md object-contain"
                            />
                            <Avatar
                                v-else
                                class="w-20 h-20"
                                size="large"
                            />
                            <input
                                ref="voucherImageInput"
                                id="voucher_image_input"
                                type="file"
                                class="hidden"
                                accept="image/*"
                                @change="handleUpload"
                            />
                            <div class="flex flex-col md:flex-row justify-end items-end gap-5 self-stretch">
                                <Button
                                    type="button"
                                    severity="secondary"
                                    outlined
                                    :label="$t('public.upload')"
                                    @click.prevent="$refs.voucherImageInput.click()"
                                />
                                <Button
                                    type="button"
                                    severity="danger"
                                    outlined
                                    :label="$t('public.remove')"
                                    :disabled="!selectedVoucherImage"
                                    @click.prevent="removePhoto"
                                />
                            </div>
                        </div>
                        <InputError :message="errors?.voucher_thumbnail?.[0]" />
                    </div>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold flex gap-1 items-start">
                        {{ $t('public.voucher_highlight') }} <span class="text-red-500 text-xs pt-1">*</span>
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
                        {{ $t('public.voucher_highlight_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <TipTapEditor
                    v-model="modelValue.voucher_highlight"
                    :invalid="errors?.voucher_highlight?.[0]"
                />
                <div class="p-3">
                    <InputError :message="errors?.voucher_highlight?.[0]" />
                </div>
            </template>
        </Card>
    </div>
</template>
