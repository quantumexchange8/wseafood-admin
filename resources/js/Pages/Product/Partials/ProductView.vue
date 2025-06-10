<script setup>
import { Card, DataView, IconField, InputIcon, InputText, Button, ToggleSwitch, Avatar, SelectButton, ProgressSpinner } from 'primevue';
import {FilterMatchMode} from "@primevue/core/api";
import { usePage } from '@inertiajs/vue3';
import { ref, watch, defineProps, watchEffect, onMounted } from 'vue';
import { debounce } from 'lodash';
import { IconSearch, IconAdjustments, IconXboxX, IconLayoutListFilled, IconLayoutGridFilled } from '@tabler/icons-vue';
import Empty from '@/Components/Empty.vue';
import {generalFormat} from "@/Composables/format.js";
import {useLangObserver} from "@/Composables/localeObserver.js";

const props = defineProps({
    categories: Object,
});

const { formatNameLabel, formatAmount } = generalFormat();
const { locale } = useLangObserver();

const layout = ref('grid');
const options = ref(['list', 'grid']);
const isLoading = ref(false);
// const dt = ref(null);
const fetchedProduct = ref([]);
// const totalRecords = ref(0);
const first = ref(0);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
    name: { value: null, matchMode: FilterMatchMode.EQUALS },
    category: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const lazyParams = ref({});

const loadLazyData = (event) => {
    isLoading.value = true;

    lazyParams.value = { ...lazyParams.value, first: event?.first || first.value };
    lazyParams.value.filters = filters.value;
    try {
        setTimeout(async () => {
            const params = {
                // page: JSON.stringify(event?.page + 1),
                // sortField: event?.sortField,
                // sortOrder: event?.sortOrder,
                include: [],
                lazyEvent: JSON.stringify(lazyParams.value)
            };

            const url = route('product.fetch', params);
            const response = await fetch(url);
            const results = await response.json();

            fetchedProduct.value = results?.data;

            // totalRecords.value = results?.data?.total;
            isLoading.value = false;

        }, 100);
    }  catch (e) {
        fetchedProduct.value = [];
        // totalRecords.value = 0;
        isLoading.value = false;
    }
};

// const onPage = (event) => {
//     lazyParams.value = event;
//     loadLazyData(event);
// };
// const onSort = (event) => {
//     lazyParams.value = event;
//     loadLazyData(event);
// };
// const onFilter = (event) => {
//     lazyParams.value.filters = filters.value ;
//     loadLazyData(event);
// };

//filter status
const status = ref([
    { label: 'active', value: '1',},
    { label: 'inactive', value: '0',}
]);

//filter toggle
const op = ref();
const toggle = (event) => {
    op.value.toggle(event);
};

onMounted(() => {
    lazyParams.value = {
        // first: dt.value.first,
        // rows: dt.value.rows,
        // sortField: null,
        // sortOrder: null,
        filters: filters.value
    };

    loadLazyData();
})

watch(
    filters.value['global'],
    debounce(() => {
        loadLazyData();
    }, 300)
);

watch([filters.value['status']], () => {
    loadLazyData()
});

const clearAll = () => {
    filters.value['global'].value = null;
    filters.value['status'].value = null;
};

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

watchEffect(() => {
    if (usePage().props.toast !== null) {
        loadLazyData();
    }
});

const selectedIndex = ref('all');
function selectCategory(index) {
    selectedIndex.value = index;

    if(index === 'all') {
        filters.value['category'].value = null;
    }
    else {
        filters.value['category'].value = props.categories[index].id;
    }

    loadLazyData()
}
</script>

<template>
    <DataView :value="fetchedProduct" :layout="layout" class="w-full">
        <template #empty>
            <div v-if="isLoading">
                <div class="flex flex-col justify-center items-center gap-5 self-stretch">
                    <ProgressSpinner
                        strokeWidth="4"
                        class="w-16 h-16"
                    />
                    <span class="text-sm font-semibold text-surface-700 dark:text-surface-300">
                        {{ $t('public.loading_data') }}
                    </span>
                </div>
            </div>

            <div v-if="fetchedProduct.length === 0 && !isLoading">
                <Empty
                    :title="$t('public.no_data_found')"
                />
            </div>
        </template>

        <template #header>
            <div class="flex justify-between items-center self-stretch">
                <div class="flex flex-col md:flex-row items-center self-stretch gap-3 w-full md:w-auto">
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
                    <!-- filter button -->
                    <Button
                        class="flex gap-2 items-center w-full md:w-fit font-bold !bg-white"
                        severity="secondary"
                        outlined
                        @click="toggle"
                    >
                        <IconAdjustments :size="16" stroke-width="1.5"/>
                        {{ $t('public.filter') }}
                    </Button>
                </div>
                <div class="flex items-center gap-3">
                    <SelectButton v-model="layout" :options="options" :allowEmpty="false">
                        <template #option="{ option }">
                            <IconLayoutListFilled
                                v-if="option==='list'"
                                :size="20"
                            />
                            <IconLayoutGridFilled
                                v-else
                                :size="20"
                            />
                        </template>
                    </SelectButton>
                </div>
            </div>
            <div class="w-full pt-5 flex items-center gap-3 overflow-x-auto">
                <div
                    @click="selectCategory('all')"
                    :class="[
                        'min-w-fit py-3 px-4 rounded-lg text-sm font-bold text-nowrap transition-colors hover:cursor-pointer',
                        selectedIndex === 'all'
                            ? 'bg-primary text-white hover:bg-primary-700'
                            : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
                        ]"
                >
                    {{ $t('public.all') }}
                </div>
                <div
                    v-for="(category, index) in categories"
                    @click="selectCategory(index)"
                    :class="[
                        'min-w-fit py-3 px-4 rounded-lg text-sm font-bold text-nowrap transition-colors hover:cursor-pointer',
                        selectedIndex === index
                            ? 'bg-primary text-white hover:bg-primary-700'
                            : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
                        ]"
                >
                    {{ category.name[locale] ?? category.name['en'] }}
                </div>
            </div>
        </template>

        <template #grid="slotProps">
            <div class="pt-5 grid grid-cols-3 xl:grid-cols-4 items-stretch content-start gap-4 shrink-0 self-stretch">
                <Card
                    v-for="(product, index) in slotProps.items"
                    :key="index"
                    class="min-w-[240px] flex flex-col items-start self-stretch"
                >
                    <template #header>
                        <div class="w-full p-4 flex justify-center items-center">
                            <div class="w-full px-3 py-10 flex justify-center items-center self-stretch rounded-lg bg-slate-100">
                                <Avatar
                                    v-if="product.product_photo"
                                    :image="product.product_photo"
                                    class="w-40 h-40"
                                />
                                <Avatar
                                    v-else
                                    :label="formatNameLabel(product.name[locale] ?? product.name['en'])"
                                    class="w-40 h-40"
                                    size="large"
                                />
                            </div>
                        </div>
                    </template>
                    <template #title>
                        <div class="px-4 pb-1.5 font-bold">
                            {{ product.name[locale] ?? product.name['en'] }}
                        </div>
                    </template>
                    <template #content>
                        <div class="px-4 pt-1.5 pb-4">
                            <div class="flex justify-between items-end self-stretch">
                                <div>
                                    {{ formatAmount(product.price, 2, 'RM') }}
                                </div>
                                <ToggleSwitch
                                    :model-value="product.status === '1' ? true : false"
                                />
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </template>

        <template #list="slotProps">
            <div class="pt-5 grid grid-cols-2 xl:grid-cols-3 items-stretch content-start gap-4 shrink-0 self-stretch flex-wrap">
                <Card
                    v-for="(product, index) in slotProps.items"
                    :key="index"
                    class="min-w-[280px] p-4 flex flex-row items-start gap-4"
                >
                    <template #header>
                        <div class="w-full flex justify-center items-center">
                            <div class="w-full px-2 py-2 flex justify-center items-center self-stretch rounded-lg bg-slate-100">
                                <Avatar
                                    v-if="product.product_photo"
                                    :image="product.product_photo"
                                    class="w-24 h-24"
                                />
                                <Avatar
                                    v-else
                                    :label="formatNameLabel(product.name[locale] ?? product.name['en'])"
                                    class="w-24 h-24"
                                    size="large"
                                />
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <div class="h-full flex flex-col justify-between items-start self-stretch">
                            <div class="flex flex-col items-start gap-2 self-stretch">
                                <div class="font-bold">
                                    {{ product.name[locale] ?? product.name['en'] }}
                                </div>
                                <div>
                                    {{ formatAmount(product.price, 2, 'RM') }}
                                </div>
                            </div>
                            <ToggleSwitch
                                :model-value="product.status === '1' ? true : false"
                            />
                        </div>
                    </template>
                </Card>
            </div>
        </template>
    </DataView>
</template>