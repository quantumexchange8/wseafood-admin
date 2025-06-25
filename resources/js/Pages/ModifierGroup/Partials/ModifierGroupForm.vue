<script setup>
import { Card, Button, InputText, RadioButton, InputNumber, Checkbox, DataTable, Tag, Column, ToggleSwitch, ProgressSpinner } from 'primevue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {generalFormat} from "@/Composables/format.js";
import InputError from '@/Components/InputError.vue';
import {useLangObserver} from "@/Composables/localeObserver.js";
import IconAlertTooltip from '@/Components/IconAlertTooltip.vue';
import { IconPlus, IconPencil, IconTrash } from '@tabler/icons-vue';
import SelectModifierItemModal from '@/Pages/ModifierGroup/Partials/SelectModifierItemModal.vue';
import EditPriceModal from '@/Pages/ModifierGroup/Partials/EditPriceModal.vue';
import SelectMealItemModal from '@/Pages/ModifierGroup/Partials/SelectMealItemModal.vue';
import MealItemPhoto from '@/Pages/ModifierGroup/Partials/MealItemPhoto.vue';

const props = defineProps({
    itemCount: Number,
    categoryCount: Number,
    modifierGroup: Object,
    selectedItem: Object,
});

const { formatAmount } = generalFormat();
const { locale } = useLangObserver();

const availableLocales = JSON.parse(usePage().props.availableLocales);

const noMax = ref(false);
const selectModalVisible = ref(false);
const addedItem = ref();
const addedItemUpdate = ref();
const editPriceVisible = ref(false);
const priceItem = ref(null);
const selectMealVisible = ref(false);
const selectedMeal = ref();
const selectedMealKeys = ref();
const selectMealItemModalRef = ref();

const form = useForm({
    group_name: props.modifierGroup?.group_name ?? '',
    display_name: props.modifierGroup?.display_name ?? {},
    group_type: props.modifierGroup?.group_type ?? 'optional',
    min: props.modifierGroup?.min_selection ?? 0,
    max: 1,
    modifier_items: {},
    meals: {},
    default_item: null,
});

const submitForm = () => {
    form.modifier_items = addedItemUpdate.value;
    form.meals = selectedMeal.value;

    form.post(route('modifier.group.store'), {
        onSuccess: () => {
            form.reset();
        },
        onError: () => {},
    })
};

const reorderItem = (event) => {
    addedItemUpdate.value = event.value;
}
const updateItemStatus = (id) => {
    let item = addedItemUpdate.value.find(item => item.id === id);

    if(item.status === 'active') {
        item.status = 'inactive';
    } else {
        item.status = 'active';
    }
};

const removeItem = (id) => {
    if (addedItem.value && id !== undefined) {
        addedItem.value = addedItem.value.filter(item => item.id !== id);
    }
}

const priceModal = (item) => {
    priceItem.value = item;
    editPriceVisible.value = true;
}

const updateItemPrice = (data) => {
    const id = data.id;
    const price = data.price;
    const item = addedItemUpdate.value.find(item => item.id === id);
    item.price = price;
}

const assignMeal = (data) => {
    selectedMeal.value = data[0];
    selectedMealKeys.value = data[1];
}

const onRemoveMealItem = (product) => {
    // Remove from selectedMeal
    if (selectedMeal.value && Array.isArray(selectedMeal.value)) {
        selectedMeal.value = selectedMeal.value.filter(item => item.key !== product.key);
    }
    // Ask the modal to update its selectedkey and emit the new keys
    if (selectMealItemModalRef.value) {
        selectMealItemModalRef.value.removeNodeAndChildren(product.key);
    }
};

watch((noMax), () => {
    if(noMax.value) {
        form.max = null;
    } else {
        form.max = 1;
    }
});

watch(() => props.modifierGroup, () => {
    if(props.modifierGroup?.max_selection === null) {
        noMax.value = true;
    }
}, {immediate: true, deep: true});

watch(() => props.selectedItem, (val) => {
    if (val && Array.isArray(val)) {
        addedItem.value = val;
        addedItemUpdate.value = val;

        const defaultItem = val.find(item => item.default === 1);
        form.default_item = defaultItem ? defaultItem.id : null;
    }
}, { immediate: true, deep: true });

watch(() => form.group_type, () => {
    if(form.group_type === 'optional') {
        form.min = 0;
    } else {
        form.min = 1;
    }
});

watch((addedItem), () => {
    if(addedItem.value.length > 0 && !addedItemUpdate.value) {
        addedItemUpdate.value = addedItem.value;
        
        addedItemUpdate.value.forEach(item => {
            item.status = 'active';
            item.price = 0;
        });
    }

    if (addedItem.value && addedItemUpdate.value) {
        // Remove items from addedItemUpdate that are no longer in addedItem
        addedItemUpdate.value = addedItemUpdate.value.filter(item =>
            addedItem.value.some(newItem => newItem.id === item.id)
        );

        // Add new items from addedItem to addedItemUpdate
        addedItem.value.forEach(newItem => {
            if (!addedItemUpdate.value.some(item => item.id === newItem.id)) {
                newItem.status = 'active';
                newItem.price = 0;

                addedItemUpdate.value.push({
                    ...newItem
                });
            }
        });
    }

    if(addedItem.value.length < 1) {
        addedItemUpdate.value = null;
    }
});

</script>

<template>
    <form @submit.prevent="submitForm" class="flex flex-col items-start gap-4 self-stretch">
        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.group_detail') }}
                    </div>
                    <div class="font-normal italic text-xs text-gray-400">
                        {{ $t('public.group_detail_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="py-5 flex flex-col items-start gap-5 self-stretch">
                    <div class="px-5 flex items-center gap-5 self-stretch">
                        <div class="w-1/5 flex items-center gap-1">
                            <label for="group-name" class="text-sm">
                                {{ $t('public.group_name') }}
                            </label>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <InputText
                            v-model="form.group_name"
                            id="group-name"
                            class="w-1/3"
                            :placeholder="$t('public.group_name_placeholder')"
                        />
                        <InputError :message="form.errors.group_name"/>
                    </div>

                    <div 
                        v-for="lang in availableLocales"
                        :key="lang"
                        class="px-5 flex items-center gap-5 self-stretch"
                    >
                        <div class="w-1/5 flex items-center gap-1">
                            <label :for="`display-name-${lang.value}`" class="text-sm">
                                {{ $t('public.display_name') }} ({{ lang.label }})
                            </label>
                            <IconAlertTooltip :message="$t('public.display_name_tooltip')" />
                        </div>
                        <InputText
                            v-model="form.display_name[lang.value]"
                            :id="`display-name-${lang.value}`"
                            class="w-1/3"
                            :placeholder="$t('public.display_name_placeholder')"
                        />
                        <InputError :message="form.errors[`display_name.${lang.value}`]" />
                    </div>

                    <div class="px-5 flex items-center gap-5 self-stretch">
                        <div class="w-1/5 flex items-center gap-1">
                            <div class="text-sm">
                                {{ $t('public.group_type') }}
                            </div>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <div class="flex items-center gap-5">
                            <div class="flex items-center gap-3">
                                <RadioButton v-model="form.group_type" inputId="required" value="required" />
                                <div class="flex items-center gap-2">
                                    <label for="required" class="text-sm">
                                        {{ $t('public.required') }}
                                    </label>
                                    <IconAlertTooltip :message="$t('public.required_tooltip')" />
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <RadioButton v-model="form.group_type" inputId="optional" value="optional" />
                                <div class="flex items-center gap-2">
                                    <label for="optional" class="text-sm">
                                        {{ $t('public.optional') }}
                                    </label>
                                    <IconAlertTooltip :message="$t('public.optional_tooltip')" />
                                </div>
                            </div>
                        </div>
                        <InputError :message="form.errors.group_type" />
                    </div>

                    <div class="px-5 flex items-center gap-5 self-stretch">
                        <div class="w-1/5 flex items-center gap-1">
                            <label for="max" class="text-sm">
                                {{ $t('public.min_max_selection') }}
                            </label>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="relative w-40">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                                Min.
                                </span>
                                <InputNumber
                                    v-model="form.min"
                                    placeholder="0"
                                    :min="0"
                                    inputClass="pl-12 w-full text-center"
                                    readonly
                                />
                            </div>
                            
                            <div class="flex items-center">
                                <div class="w-16 h-px bg-slate-400"></div>
                            </div>

                            <div class="relative w-40">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                                Max.
                                </span>
                                <InputText
                                    v-if="noMax"
                                    modelValue="∞"
                                    class="pl-12 w-full text-[18px] text-center"
                                    readonly
                                />
                                <InputNumber
                                    v-else
                                    v-model="form.max"
                                    placeholder="0"
                                    :min="0"
                                    inputClass="pl-12 w-full text-center"
                                />
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <Checkbox 
                                v-model="noMax" 
                                inputId="no_max"
                                binary 
                            />
                            <label for="no_max">
                                {{ $t('public.no_max_selection') }}
                            </label>
                        </div>
                        <InputError :message="form.errors.max"/>
                    </div>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="flex items-center gap-4">
                        <div class="text-lg font-bold">
                            {{ $t('public.add_modifier_item') }}
                        </div>
                        <Tag 
                            v-if="addedItemUpdate" 
                            rounded
                        >
                            {{ addedItemUpdate.length }} {{ $t('public.items') }}
                        </Tag>
                    </div>
                    <Button
                        v-if="addedItemUpdate"
                        type="button"
                        size="small"
                        variant="text"
                        :label="$t('public.add_another_item')"
                        @click="selectModalVisible = true"
                    >
                        <template #icon>
                            <IconPlus :size="20"/>
                        </template>
                    </Button>
                </div>
            </template>
            <template #content>
                <DataTable
                    v-if="addedItemUpdate"
                    :value="addedItemUpdate"
                    @rowReorder="reorderItem"
                >
                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner
                                strokeWidth="4"
                                class="w-10 h-10"
                            />
                            <span class="text-sm font-semibold text-surface-700 dark:text-surface-300">
                                {{ $t('public.loading_data') }}
                            </span>
                        </div>
                    </template>

                    <template v-if="addedItemUpdate.length > 0">
                        <Column
                            field="status"
                            class="w-[100px]"
                            :header="$t('public.status')"
                        >
                            <template #body="{ data }">
                                <div class="flex justify-center items-center">
                                    <ToggleSwitch
                                        :model-value="data.status"
                                        true-value="active"
                                        false-value="inactive"
                                        class="flex items-center"
                                        @change="updateItemStatus(data.id)"
                                    />
                                </div>
                            </template>
                        </Column>
                        <Column
                            field="modifier_name"
                            :header="$t('public.modifier_name')"
                        >
                            <template #body="{ data }">
                                <span class="text-sm font-bold">
                                    {{ JSON.parse(data.name)[locale] ?? JSON.parse(data.name)['en'] }}
                                </span>
                            </template>
                        </Column>
                        <Column
                            field="price"
                            class="w-[110px]"
                            :header="$t('public.price')"
                        >
                            <template #body="{ data }">
                                <span class="text-sm text-nowrap">
                                    + {{ formatAmount(data.price, 2, 'RM') }}
                                </span>
                            </template>
                        </Column>
                        <Column
                            field="default"
                            class="w-[100px]"
                            :header="$t('public.default')"
                        >
                            <template #body="{ data }">
                                <div class="flex justify-center items-center">
                                    <RadioButton
                                        v-model="form.default_item"
                                        :value="data.id"
                                    />
                                </div>
                            </template>
                        </Column>
                        <Column
                            field="action"
                            class="w-[100px]"
                            :header="$t('public.action')"
                        >
                            <template #body="{ data }">
                                <div class="flex items-center gap-3">
                                    <Button
                                        type="button"
                                        severity="secondary"
                                        outlined
                                        size="small"
                                        class="rounded-full"
                                        icon="IconPencil"
                                        @click="priceModal(data)"
                                    >
                                        <template #icon>
                                            <IconPencil :size="14" stroke-width="1.5"/>
                                        </template>
                                    </Button>
                                    <Button
                                        type="button"
                                        severity="secondary"
                                        outlined
                                        size="small"
                                        class="rounded-full"
                                        @click="removeItem(data.id)"
                                    >
                                        <template #icon>
                                            <IconTrash :size="16" stroke-width="1.5" class="text-red-500"/>
                                        </template>
                                    </Button>

                                </div>
                            </template>
                        </Column>
                        <Column rowReorder class="w-12" :reorderableColumn="false" />
                    </template>
                </DataTable>
                <div 
                    v-else
                    class="p-5 flex items-center self-stretch gap-4"
                >
                    <Button
                        type="button"
                        :label="$t('public.add')"
                        @click="selectModalVisible = true"
                    >
                        <template #icon>
                            <IconPlus :size="20" />
                        </template>
                    </Button>

                    <InputError :message="form.errors.modifier_items" />
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex items-center self-stretch gap-4">
                    <div class="text-lg font-bold">
                        {{ $t('public.add_to_meal_item') }}
                    </div>
                    <Tag 
                        v-if="selectedMeal && selectedMeal.length > 0" 
                        rounded
                    >
                        {{ selectedMeal.length }} {{ $t('public.linked_items') }}
                    </Tag>
                </div>
            </template>
            <template #content>
                <div class="p-5 flex items-center self-stretch gap-4">
                    <div 
                        v-if="selectedMeal && selectedMeal.length > 0"
                        class="flex items-center gap-4"
                    >
                        <MealItemPhoto 
                            :products="selectedMeal"
                            @remove="onRemoveMealItem"
                        />
                        <div 
                            class="w-32 h-32 flex flex-col justify-center items-center gap-2.5 rounded-lg border border-dashed border-primary-500 bg-primary-50 cursor-pointer hover:bg-primary-100 transition-colors"
                            @click="selectMealVisible = true"
                        >
                            <div class="flex flex-col justify-center items-center gap-1">
                                <IconPlus :size="24" class="text-primary-400" />
                                <div class="text-sm text-primary-500">
                                    {{ $t('public.add_more') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <Button
                        v-else
                        type="button"
                        :label="$t('public.add')"
                        @click="selectMealVisible = true"
                    >
                        <template #icon>
                            <IconPlus :size="20" />
                        </template>
                    </Button>
                    <InputError :message="form.errors.meals" />
                </div>
            </template>
        </Card>

        <div class="w-full px-5 py-4 flex justify-between items-center border-t border-solid border-primary-200 bg-white shadow-sm">
            <Button
                type="button"
                severity="secondary"
                outlined
                :label="$t('public.cancel')"
                @click="() => $inertia.visit(route('modifier.group.index'))"
            />
            <Button
                type="submit"
                severity="primary"
                :label="$t('public.submit')"
                :disabled="form.processing"
            />
        </div>
    </form>

    <SelectModifierItemModal
        :visible="selectModalVisible" 
        :itemCount="itemCount"
        :updateChecked="addedItem"
        @update:visible="selectModalVisible = $event" 
        @update:addItem="addedItem = $event"
    />

    <EditPriceModal 
        :visible="editPriceVisible" 
        :item="priceItem" 
        @update:visible="editPriceVisible = $event" 
        @update:priceUpdated="updateItemPrice($event)"
    />

    <SelectMealItemModal
        ref="selectMealItemModalRef"
        :visible="selectMealVisible"
        :categoryCount="categoryCount"
        :updateSelected="selectedMealKeys"
        @update:visible="selectMealVisible = $event" 
        @update:addMeal="assignMeal($event)"
        @update:selectedKeys="selectedMealKeys = $event"
    />
</template>