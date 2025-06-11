<script setup>
import { Card, Button, InputText, RadioButton, Avatar,Select, InputNumber } from 'primevue';
import { useForm, usePage } from '@inertiajs/vue3';
import { onMounted, ref, watchEffect } from 'vue';
import {generalFormat} from "@/Composables/format.js";
import axios from 'axios';
import { IconPlus, IconUpload } from '@tabler/icons-vue';
import CreateCategoryModal from '@/Pages/Product/Partials/CreateCategoryModal.vue';
import InputError from '@/Components/InputError.vue';
import {useLangObserver} from "@/Composables/localeObserver.js";

const props = defineProps({
    product: Object,
});

const { locale } = useLangObserver();

const categories = ref([]);
const loadingCategories = ref(false);
const createCategoryModal = ref(false);
const newCategoryFlag = ref(false);

const form = useForm({
    name: {},
    status: '',
    product_photo: null,
    photo_action:'',
    sale_price: '',
    category_id: '',
});

const getCategories = async () => {
    loadingCategories.value = true;
    try {
        const response = await axios.get(route('category.fetch'));
        categories.value = response.data.data;

        if(newCategoryFlag.value) {
            form.category_id = categories.value.length > 0 ? categories.value[categories.value.length - 1].id : '';
            newCategoryFlag.value = false;
        }

    } catch (error) {
        console.error('Error fetching categories:', error);
    } finally {
        loadingCategories.value = false;
    }
}

onMounted(() => {
    getCategories();
});

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getCategories();
    }
});

const { formatNameLabel } = generalFormat();

const selectedProductPhoto = ref(null) //ref(props.category.category_thumbnail);
const handleUploadProductPhoto = (event) => {
    const productPhotoInput = event.target;
    const file = productPhotoInput.files[0];

    if (file) {
        // Display the selected image
        const reader = new FileReader();
        reader.onload = () => {
            selectedProductPhoto.value = reader.result;
        };
        reader.readAsDataURL(file);
        form.product_photo = event.target.files[0];
        form.photo_action = '';
    } else {
        selectedProductPhoto.value = null;
    }
};

const removeProductPhoto = () => {
    selectedProductPhoto.value = null;
    form.product_photo = null;
    form.photo_action = 'remove';
};

const submitForm = () => {
    emit('formSubmitted', true);

    form.post(route('product.store'), {
        onSuccess: () => {
            form.reset();
            emit('formSubmitted', false);
        },
        onError: () => {
            emit('formSubmitted', false);
        },
    })
};

const availableLocales = JSON.parse(usePage().props.availableLocales);

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
                        {{ $t('public.meal_detail') }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $t('public.meal_detail_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="py-5 flex flex-col items-start gap-5 self-stretch">
                    <div
                        v-for="lang in availableLocales"
                        :key="lang"
                        class="px-5 flex items-center gap-5 self-stretch"
                    >
                        <div class="w-1/5 flex items-center gap-1">
                            <label :for="`item-name-${lang.value}`" class="text-sm">
                                {{ $t('public.item_name') }} ({{ lang.label }})
                            </label>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <InputText
                            v-model="form.name[lang.value]"
                            :id="`item-name-${lang.value}`"
                            class="w-1/3"
                            :placeholder="$t('public.meal_item_name_placeholder')"
                        />
                        <InputError :message="form.errors[`name.${lang.value}`]" />
                    </div>

                    <div class="px-5 flex items-center gap-5 self-stretch">
                        <div class="w-1/5 flex items-center gap-1">
                            <label for="category_field" class="text-sm">
                                {{ $t('public.category') }}
                            </label>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <Select
                            v-model="form.category_id"
                            labelId="category_field"
                            :options="categories"
                            optionValue="id"
                            optionLabel="name"
                            :placeholder="$t('public.select_category')"
                            class="w-1/3"
                            showClear
                            filter
                            :loading="loadingCategories"
                        >
                            <template #value="slotProps">
                                <div v-if="slotProps.value" class="flex items-center gap-2">
                                    <Avatar
                                        v-if="categories.find(c => c.id === slotProps.value)?.category_thumbnail"
                                        :image="categories.find(c => c.id === slotProps.value).category_thumbnail"
                                        class="w-6 h-6"
                                    />
                                    <Avatar
                                        v-else
                                        :label="formatNameLabel(JSON.parse(categories.find(c => c.id === slotProps.value)?.name)[locale] ?? JSON.parse(categories.find(c => c.id === slotProps.value)?.name)['en'])"
                                        class="w-6 h-6 text-xs"
                                        size="large"
                                    />
                                    <span>
                                        {{ JSON.parse(categories.find(c => c.id === slotProps.value)?.name)[locale] ?? JSON.parse(categories.find(c => c.id === slotProps.value)?.name)['en'] }}
                                    </span>
                                </div>
                                <span v-else>{{ slotProps.placeholder }}</span>
                            </template>

                            <template #option="slotProps">
                                <div class="flex items-center gap-2">
                                    <Avatar
                                        v-if="slotProps.option.category_thumbnail"
                                        :image="slotProps.option.category_thumbnail"
                                        class="w-10 h-10"
                                    />
                                    <Avatar
                                        v-else
                                        :label="formatNameLabel(JSON.parse(slotProps.option.name)[locale] ?? JSON.parse(slotProps.option.name)['en'])"
                                        class="w-10 h-10"
                                        size="large"
                                    />
                                    <span>
                                        {{ JSON.parse(slotProps.option.name)[locale] ?? JSON.parse(slotProps.option.name)['en'] }}
                                    </span>
                                </div>
                            </template>

                            <template #footer>
                                <div class="p-4 flex items-center justify-center">
                                    <Button
                                        type="button"
                                        severity="secondary"
                                        outlined
                                        :label="$t('public.create_new_category')"
                                        @click="createCategoryModal = true"
                                        class="w-full"
                                    >
                                        <template #icon>
                                            <IconPlus :size="20"/>
                                        </template>
                                    </Button>
                                </div>
                            </template>
                        </Select>
                        <InputError :message="form.errors.category_id" />
                    </div>

                    <div class="px-5 flex items-center gap-5 self-stretch">
                        <div class="w-1/5 flex items-center gap-1">
                            <label for="price" class="text-sm">
                                {{ $t('public.sale_price') }}
                            </label>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <div class="w-1/3">
                            <InputNumber
                                v-model="form.sale_price"
                                inputId="price"
                                class="w-full"
                                placeholder="0.00"
                                :min="0"
                                :maxFractionDigits="2"
                                prefix="RM "
                            />
                        </div>
                        <InputError :message="form.errors.sale_price" />
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
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.modifier_group') }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $t('public.modifier_group_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="px-5 py-5">
                    <Button
                        type="button"
                        :label="$t('public.add_group')"
                    >
                        <template #icon>
                            <IconPlus :size="20"/>
                        </template>
                    </Button>
                </div>
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
                    ref="productPhotoInput"
                    id="product_photo_input"
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="handleUploadProductPhoto"
                />
                <div class="px-5 py-5 flex items-center gap-4">
                    <Button
                        v-if="!selectedProductPhoto"
                        type="button"
                        :label="$t('public.upload')"
                        @click.prevent="$refs.productPhotoInput.click()"
                    >
                        <template #icon>
                            <IconUpload :size="20"/>
                        </template>
                    </Button>
                    <div v-if="selectedProductPhoto" class="flex items-end gap-5 self-stretch">
                        <Avatar
                            v-if="selectedProductPhoto"
                            :image="selectedProductPhoto"
                            class="w-20 h-20"
                        />
                        <input
                            ref="productPhotoInput"
                            id="product_photo_input"
                            type="file"
                            class="hidden"
                            accept="image/*"
                            @change="handleUploadProductPhoto"
                        />
                        <div class="flex flex-col md:flex-row justify-end items-end gap-5 self-stretch">
                            <Button
                                type="button"
                                severity="secondary"
                                outlined
                                :label="$t('public.change')"
                                @click.prevent="$refs.productPhotoInput.click()"
                            />
                            <Button
                                type="button"
                                severity="danger"
                                outlined
                                :label="$t('public.remove')"
                                :disabled="!selectedProductPhoto || form.processing"
                                @click.prevent="removeProductPhoto"
                            />
                        </div>
                    </div>
                    <InputError :message="form.errors.product_photo" />
                </div>
            </template>
        </Card>
    </form>

    <CreateCategoryModal 
        :visible="createCategoryModal" 
        @update:visible="createCategoryModal = $event" 
        @update:category="newCategoryFlag = $event"
    />
</template>