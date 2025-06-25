<script setup>
import { Card, Button, InputText, RadioButton, Avatar, Select, InputNumber, Tag, ToggleSwitch } from 'primevue';
import { useForm, usePage } from '@inertiajs/vue3';
import { onMounted, ref, watchEffect, watch } from 'vue';
import {generalFormat} from "@/Composables/format.js";
import axios from 'axios';
import { IconPlus, IconUpload, IconTrash } from '@tabler/icons-vue';
import CreateCategoryModal from '@/Pages/Product/Partials/CreateCategoryModal.vue';
import InputError from '@/Components/InputError.vue';
import {useLangObserver} from "@/Composables/localeObserver.js";
import TipTapEditor from '@/Components/TipTapEditor.vue';
import SelectModifierGroupModal from '@/Pages/Product/Partials/SelectModifierGroupModal.vue';

const props = defineProps({
    product: Object,
    groupCount: Number,
});

const { locale } = useLangObserver();

const categories = ref([]);
const loadingCategories = ref(false);
const createCategoryModal = ref(false);
const newCategoryFlag = ref(false);
const selectModifierGroupModal = ref(false);
const addedGroup = ref();
const addedGroupUpdate = ref();

const form = useForm({
    name: {},
    status: '',
    product_photo: null,
    photo_action:'',
    sale_price: '',
    category_id: '',
    description: '',
    modifier_group: null,
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

const selectedProductPhoto = ref(null);
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
    form.modifier_group = addedGroupUpdate.value;

    form.post(route('product.store'), {
        onSuccess: () => {
            form.reset();
        },
    })
};

const availableLocales = JSON.parse(usePage().props.availableLocales);

const updateGroupStatus = (id) => {
    let item = addedGroupUpdate.value.find(item => item.id === id);

    if(item.status === 'active') {
        item.status = 'inactive';
    } else {
        item.status = 'active';
    }
};

const removeGroup = (id) => {
    if (addedGroup.value && id !== undefined) {
        addedGroup.value = addedGroup.value.filter(item => item.id !== id);
    }
}

watch((addedGroup), () => {
    if(addedGroup.value.length > 0 && !addedGroupUpdate.value) {
        addedGroupUpdate.value = addedGroup.value;
        
        addedGroupUpdate.value.forEach(item => {
            item.status = 'active';
            item.price = 0;
        });
    }

    if (addedGroup.value && addedGroupUpdate.value) {
        // Remove items from addedGroupUpdate
        addedGroupUpdate.value = addedGroupUpdate.value.filter(item =>
            addedGroup.value.some(newItem => newItem.id === item.id)
        );

        // Add new items
        addedGroup.value.forEach(newItem => {
            if (!addedGroupUpdate.value.some(item => item.id === newItem.id)) {
                newItem.status = 'active';
                newItem.price = 0;

                addedGroupUpdate.value.push({
                    ...newItem
                });
            }
        });
    }

    if(addedGroup.value.length < 1) {
        addedGroupUpdate.value = null;
    }
});

const getSeverity = (type) => {
    switch (type) {
        case 'required':
            return 'danger';

        case 'optional':
            return 'secondary';
    }
};

</script>

<template>
    <form @submit.prevent="submitForm" class="flex flex-col items-start gap-4 self-stretch">
        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.meal_detail') }}
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
                        {{ $t('public.meal_detail_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="p-5 grid grid-cols-4 items-center gap-5 self-stretch">
                    <template
                        v-for="lang in availableLocales"
                        :key="lang"
                    >
                        <div class="w-full flex items-center gap-1">
                            <label :for="`item-name-${lang.value}`" class="text-sm">
                                {{ $t('public.item_name') }} ({{ lang.label }})
                            </label>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <div class="col-span-2 flex flex-col gap-1">
                            <InputText
                                v-model="form.name[lang.value]"
                                :id="`item-name-${lang.value}`"
                                class="w-full"
                                :placeholder="$t('public.meal_item_name_placeholder')"
                                :invalid="!!form.errors[`name.${lang.value}`]"
                            />
                            <InputError :message="form.errors[`name.${lang.value}`]" />
                        </div>
                        <div></div>
                    </template>

                    <div class="w-full flex items-center gap-1">
                        <label for="category_field" class="text-sm">
                            {{ $t('public.category') }}
                        </label>
                        <div class="text-xs text-red-500">
                            *
                        </div>
                    </div>
                    <div class="col-span-2 flex flex-col gap-1">
                        <Select
                            v-model="form.category_id"
                            labelId="category_field"
                            :options="categories"
                            optionValue="id"
                            optionLabel="name"
                            :placeholder="$t('public.select_category')"
                            class="w-full"
                            showClear
                            filter
                            :loading="loadingCategories"
                            :invalid="!!form.errors.category_id"
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
                    <div></div>

                    <div class="w-full flex items-center gap-1">
                        <label for="price" class="text-sm">
                            {{ $t('public.sale_price') }}
                        </label>
                        <div class="text-xs text-red-500">
                            *
                        </div>
                    </div>
                    <div class="col-span-2 flex flex-col gap-1">
                        <div class="relative w-full">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                            RM
                            </span>
                            <InputNumber
                                v-model="form.sale_price"
                                placeholder="0.00"
                                :min="0"
                                :maxFractionDigits="2"
                                inputClass="pl-12"
                                inputId="price"
                                fluid
                                :invalid="form.errors.sale_price"
                            />
                        </div>
                        <InputError :message="form.errors.sale_price" />
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
                    <div></div>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="flex items-center gap-4">
                        <div class="text-lg font-bold">
                            {{ $t('public.modifier_group') }}
                        </div>
                        <Tag 
                            v-if="addedGroupUpdate" 
                            rounded
                        >
                            {{ addedGroupUpdate.length }} {{ $t('public.groups') }}
                        </Tag>
                    </div>
                    <Button
                        v-if="addedGroupUpdate"
                        type="button"
                        size="small"
                        variant="text"
                        :label="$t('public.add_another_group')"
                        @click="selectModifierGroupModal = true"
                    >
                        <template #icon>
                            <IconPlus :size="20"/>
                        </template>
                    </Button>
                    <div 
                        v-else
                        class="font-normal italic text-xs text-gray-400"
                    >
                        {{ $t('public.modifier_group_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="px-5 py-5">
                    <div 
                        v-if="addedGroupUpdate"
                        class="grid grid-cols-3 items-stretch content-start gap-5 self-stretch flex-wrap"
                    >
                        <Card 
                            v-for="data in addedGroupUpdate"
                            class="w-full h-full flex flex-col"
                        >
                            <template #content>
                                <div class="p-4 flex flex-col items-start gap-4 self-stretch">
                                    <Tag 
                                        rounded
                                        :severity="getSeverity(data.group_type)"
                                    >
                                        {{ $t(`public.${data.group_type}`) }}
                                    </Tag>
                                    <div class="flex flex-col items-start gap-1 self-stretch">
                                        <div class="font-bold">
                                            {{ data.group_name }} ({{ data.item_count }})
                                        </div>
                                        <div class="text-sm">
                                            <template v-for="(item, index) in data.items">
                                                {{ JSON.parse(item.modifier_item_name)[locale] ?? JSON.parse(item.modifier_item_name)['en'] }}{{ index+1 === data.items.length ? '' : ', ' }}
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template #footer>
                                <div class="h-full flex items-end">
                                    <div class="w-full p-4 flex justify-between items-center">
                                        <ToggleSwitch
                                            :model-value="data.status"
                                            true-value="active"
                                            false-value="inactive"
                                            class="flex items-center"
                                            @change="updateGroupStatus(data.id)"
                                        />
                                        <div class="flex items-center gap-3">
                                            <Button
                                                type="button"
                                                severity="secondary"
                                                outlined
                                                size="small"
                                                class="rounded-full"
                                                @click="removeGroup(data.id)"
                                            >
                                                <template #icon>
                                                    <IconTrash :size="16" stroke-width="1.5" class="text-red-500"/>
                                                </template>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>
                    <Button
                        v-else
                        type="button"
                        :label="$t('public.add_group')"
                        @click="selectModifierGroupModal = true"
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
                    <div class="font-normal italic text-xs text-gray-400">
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

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.description') }}
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
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
                @click="() => $inertia.visit(route('product.index'))"
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

    <CreateCategoryModal 
        :visible="createCategoryModal" 
        @update:visible="createCategoryModal = $event" 
        @update:category="newCategoryFlag = $event"
    />

    <SelectModifierGroupModal
        :visible="selectModifierGroupModal"
        :groupCount="groupCount"
        :updateChecked="addedGroup"
        @update:visible="selectModifierGroupModal = $event"
        @update:addGroup="addedGroup = $event"
    />
</template>