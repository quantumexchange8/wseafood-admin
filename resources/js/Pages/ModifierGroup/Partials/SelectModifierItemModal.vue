<script setup>
import { Dialog, IconField, InputIcon, InputText, Button, DataView, Checkbox, useToast } from 'primevue';
import { IconSearch, IconXboxX, IconPlus } from '@tabler/icons-vue';
import { ref, watch, onMounted, watchEffect } from 'vue';
import {useLangObserver} from "@/Composables/localeObserver.js";
import { debounce } from 'lodash';
import {FilterMatchMode} from "@primevue/core/api";
import { usePage } from '@inertiajs/vue3';
import CreateModifierItemModal from '@/Pages/ModifierGroup/Partials/CreateModifierItemModal.vue';
import LoadingMask from '@/Pages/ModifierGroup/Partials/LoadingMask.vue';
import Empty from '@/Components/Empty.vue';
import { trans } from 'laravel-vue-i18n';

const props = defineProps({
    visible: Boolean,
    itemCount: Number,
    updateChecked: Object,
});

const emit = defineEmits(['update:visible', 'update:addItem']);

const { locale } = useLangObserver();

const toast = useToast();
const show = ref(false);
const createModalVisible = ref(false);
const isLoading = ref(false);
const dv = ref(null);
const fetchedItem = ref([]);
const newItemFlag = ref(false);
const checkedItem = ref([]);

if(props.updateChecked) {
    checkedItem.value = props.updateChecked;
}

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const lazyParams = ref({});

const loadLazyData = (event) => {
    isLoading.value = true;

    lazyParams.value.filters = filters.value;
    try {
        setTimeout(async () => {
            const params = {
                sortField: event?.sortField,
                sortOrder: event?.sortOrder,
                include: [],
                lazyEvent: JSON.stringify(lazyParams.value)
            };

            const url = route('modifier.item.fetch', params);
            const response = await fetch(url);
            const results = await response.json();

            fetchedItem.value = results?.data;
            
            if(newItemFlag.value) {
                const newItemId = fetchedItem.value.length > 0 ? fetchedItem.value[0] : '';
                checkedItem.value.push(newItemId);
                newItemFlag.value = false;
            }

            isLoading.value = false;

        }, 100);
    }  catch (e) {
        fetchedItem.value = [];
        isLoading.value = false;
    }
};

const onPage = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};
const onFilter = (event) => {
    lazyParams.value.filters = filters.value ;
    loadLazyData(event);
};

onMounted(() => {
    lazyParams.value = {
        sortField: null,
        filters: filters.value,
        rows: null,
    };

    loadLazyData();
})

watch(
    filters.value['global'],
    debounce(() => {
        loadLazyData();
    }, 300)
);

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

watchEffect(() => {
    if (usePage().props.toast !== null) {
        loadLazyData();
    }
});

watch(() => props.visible, (val) => {
    show.value = val;
});

watch(show, (val) => {
    if (val !== props.visible) {
        emit('update:visible', val);
    }
});

watch(() => props.updateChecked, () => {
    checkedItem.value = props.updateChecked;
});

const addItem = () => {
    emit('update:addItem', checkedItem.value);
    toast.add({ 
        severity: 'success', 
        summary: trans('public.modifier_item_added_success'), 
        detail: trans('public.modifier_item_added_success_caption'), 
        life: 3000 
    })
    show.value = false;
}

</script>

<template>
    <Dialog
        v-model:visible="show"
        modal
        class="dialog-sm"
    >
        <template #header>
            <div class="text-lg font-bold">
                {{ $t('public.select_modifier_item') }}
            </div>
        </template>
        
        <DataView
            :value="fetchedItem"
            class="w-full"
            :lazy="true"
            v-model:filters="filters"
            ref="dv"
            dataKey="id"
            @page="onPage($event)"
            @filter="onFilter($event)"
            :globalFilterFields="['name']"
        >
            <template #empty>
                <div
                    v-if="itemCount === 0"
                >
                    <Empty :title="$t('public.no_data_found')" />
                </div>
                <div
                    v-else
                >
                    <LoadingMask
                        v-if="isLoading"
                        :itemCount="itemCount"
                    />
                    <div 
                        v-else
                    >
                        <Empty :title="$t('public.no_data_found')" />
                    </div>
                </div>
            </template>

            <template #header>
                <div class="px-5 py-2 flex justify-between items-center self-stretch border-b border-solid">
                    <!-- Search bar -->
                    <IconField class="w-full md:w-auto">
                        <InputIcon>
                            <IconSearch :size="16" stroke-width="1.5" />
                        </InputIcon>
                        <InputText
                            v-model="filters['global'].value"
                            type="text"
                            class="block w-full pl-10 pr-10"
                        />
                        <!-- Clear filter button -->
                        <div
                            v-if="filters['global'].value"
                            class="absolute top-1/2 -translate-y-1/2 right-4 text-surface-300 hover:text-surface-400 select-none cursor-pointer"
                            @click="clearFilterGlobal"
                        >
                            <IconXboxX aria-hidden="true" :size="15" />
                        </div>
                    </IconField>
                    <Button
                        type="button"
                        severity="contrast"
                        :label="$t('public.create')"
                        @click="createModalVisible = true"
                    >
                        <template #icon>
                            <IconPlus :size="20" />
                        </template>
                    </Button>
                </div>
            </template>

            <template #list="slotProps">
                <LoadingMask
                    v-if="isLoading"
                    :itemCount="fetchedItem.length"
                />

                <div
                    v-else
                    class="flex flex-col items-start self-stretch"
                >
                    <div 
                        v-for="(item, index) in slotProps.items"
                        :key="index"
                        class="w-full py-4 px-5 flex items-center justify-between border-b border-solid border-slate-200"
                    >
                        <label :for="`item-${item.id}`" class="flex flex-col items-start gap-1">
                            <div class="font-bold">
                                {{ JSON.parse(item.name)[locale] ?? JSON.parse(item.name)['en'] }}
                            </div>
                        </label>
                        <Checkbox 
                            v-model="checkedItem"
                            :value="item"
                            :inputId="`item-${item.id}`"
                        />
                    </div>
                </div>
            </template>
        </DataView>

        <template #footer>
            <Button
                type="button"
                :label="$t('public.cancel')"
                severity="secondary"
                outlined
                @click="show = false"
            />
            <Button
                type="submit"
                :label="$t('public.add')"
                @click="addItem"
            />
        </template>
    </Dialog>

    <CreateModifierItemModal
        :visible="createModalVisible" 
        @update:visible="createModalVisible = $event" 
        @update:itemCreated="newItemFlag = $event"
    />
</template>