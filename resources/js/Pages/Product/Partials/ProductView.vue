<script setup>
import { Card, DataView, IconField, InputIcon, InputText, Button, ToggleSwitch, SelectButton, Popover, Select } from 'primevue';
import {FilterMatchMode} from "@primevue/core/api";
import { usePage } from '@inertiajs/vue3';
import { ref, watch, defineProps, watchEffect, onMounted } from 'vue';
import { debounce } from 'lodash';
import { IconSearch, IconAdjustments, IconXboxX, IconLayoutListFilled, IconLayoutGridFilled } from '@tabler/icons-vue';
import Empty from '@/Components/Empty.vue';
import {generalFormat} from "@/Composables/format.js";
import {useLangObserver} from "@/Composables/localeObserver.js";
import LoadingMask from '@/Pages/Product/Partials/LoadingMask.vue';
import ProductPhoto from '@/Pages/Product/Partials/ProductPhoto.vue';
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';

const props = defineProps({
    categories: Object,
    productCount: Number,
});

const { formatAmount } = generalFormat();
const { locale } = useLangObserver();

const layout = ref('grid');
const options = ref(['list', 'grid']);
const isLoading = ref(false);
const dv = ref(null);
const fetchedProduct = ref([]);
const totalRecords = ref(props.productCount);
const first = ref(0);
const updateStatusConfirm = ref(null);

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
                page: JSON.stringify(event?.page + 1),
                sortField: event?.sortField,
                sortOrder: event?.sortOrder,
                include: [],
                lazyEvent: JSON.stringify(lazyParams.value)
            };

            const url = route('product.fetch', params);
            const response = await fetch(url);
            const results = await response.json();

            fetchedProduct.value = results?.data?.data;

            totalRecords.value = results?.data?.total;
            isLoading.value = false;

        }, 100);
    }  catch (e) {
        fetchedProduct.value = [];
        totalRecords.value = 0;
        isLoading.value = false;
    }
};

const onPage = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};
const onSort = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};
const onFilter = (event) => {
    lazyParams.value.filters = filters.value ;
    loadLazyData(event);
};

//filter status
const status = ref(['active', 'inactive']);

//filter toggle
const op = ref();
const toggle = (event) => {
    op.value.toggle(event);
};

onMounted(() => {
    lazyParams.value = {
        first: dv.value.first,
        rows: dv.value.rows,
        sortField: null,
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

const clearAll = () => {
    filters.value['global'].value = null;
    filters.value['status'].value = null;
};

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

const applyFilter = () => {
    loadLazyData();
};

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

const updateStatus = (product) => {
    if(updateStatusConfirm.value) {
        const product_name = JSON.parse(product.name)[locale] ?? JSON.parse(product.name)['en'];
        updateStatusConfirm.value.changeStatus(product.id, product_name, product.status, 'product.updateStatus');
    } else {
        console.error("Update Status Confirmation is not available");
    }
};

</script>

<template>
    <DataView
        :value="fetchedProduct"
        :layout="layout"
        class="w-full"
        :lazy="true"
        paginator
        :rows="12"
        :first="first"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
        :currentPageReportTemplate="$t('public.paginator_caption')"
        v-model:filters="filters"
        ref="dv"
        dataKey="id"
        :totalRecords="totalRecords"
        @page="onPage($event)"
        @sort="onSort($event)"
        @filter="onFilter($event)"
        :globalFilterFields="['name', 'status']"
    >
        <template #empty>
            <div
                v-if="productCount === 0"
            >
                <Empty :title="$t('public.no_data_found')" />
            </div>
            <div
                v-else
            >
                <LoadingMask
                    v-if="isLoading"
                    :layout="layout"
                    :productCount="productCount"
                />
                <div 
                    v-else
                >
                    <Empty :title="$t('public.no_data_found')" />
                </div>
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
                    {{ JSON.parse(category.name)[locale] ?? JSON.parse(category.name)['en'] }}
                </div>
            </div>
        </template>

        <template #grid="slotProps">
            <LoadingMask
                v-if="isLoading"
                :layout="layout"
                :productCount="fetchedProduct.length"
            />
            
            <div 
                v-else
                class="py-5 grid grid-cols-3 xl:grid-cols-4 items-stretch content-start gap-4 shrink-0 self-stretch"
            >
                <Card
                    v-for="(product, index) in slotProps.items"
                    :key="index"
                    class="min-w-[240px] flex flex-col items-start self-stretch"
                >
                    <template #header>
                        <div class="w-full p-4 flex justify-center items-center">
                            <ProductPhoto 
                                :product="product"
                                :layout="layout"
                            />
                        </div>
                    </template>
                    <template #title>
                        <div class="px-4 pb-1.5 font-bold">
                            {{ product.product_code }} - {{ JSON.parse(product.name)[locale] ?? JSON.parse(product.name)['en'] }}
                        </div>
                    </template>
                    <template #content>
                        <div class="px-4 pt-1.5 pb-4">
                            <div class="flex justify-between items-end self-stretch">
                                <div>
                                    {{ formatAmount(product.price, 2, 'RM') }}
                                </div>
                                <ToggleSwitch
                                    :model-value="product.status === 'active' ? true : false"
                                    @click="updateStatus(product)"
                                    readonly
                                />
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </template>

        <template #list="slotProps">
            <LoadingMask
                v-if="isLoading"
                :layout="layout"
                :productCount="fetchedProduct.length"
            />

            <div 
                v-else
                class="py-5 grid grid-cols-2 xl:grid-cols-3 items-stretch content-start gap-4 shrink-0 self-stretch"
            >
                <Card
                    v-for="(product, index) in slotProps.items"
                    :key="index"
                    class="min-w-[280px] p-4 flex flex-row items-start gap-4"
                >
                    <template #header>
                        <div class="w-full flex justify-center items-center">
                            <ProductPhoto 
                                :product="product"
                                :layout="layout"
                            />
                        </div>
                    </template>
                    <template #content>
                        <div class="h-full flex flex-col justify-between items-start self-stretch">
                            <div class="flex flex-col items-start gap-2 self-stretch">
                                <div class="font-bold">
                                    {{ JSON.parse(product.name)[locale] ?? JSON.parse(product.name)['en'] }}
                                </div>
                                <div>
                                    {{ formatAmount(product.price, 2, 'RM') }}
                                </div>
                            </div>
                            <ToggleSwitch
                                :model-value="product.status === 'active' ? true : false"
                                @click="updateStatus(product)"
                                readonly
                            />
                        </div>
                    </template>
                </Card>
            </div>
        </template>
    </DataView>

    <Popover ref="op">
        <div class="flex flex-col gap-6 w-60">
            <!-- Filter status -->
            <div class="flex flex-col gap-4 items-center self-stretch">
                <div class="flex self-stretch text-xs text-surface-950 dark:text-white font-semibold">
                    {{ $t('public.status') }}
                </div>
                <div class="w-full flex justify-start items-start content-start gap-2">
                    <Button
                        v-for="data in status"
                        type="button"
                        severity="secondary"
                        rounded
                        :class="{'bg-gray-300' : filters['status'].value === data},
                                'bg-white'"
                        size="small"
                        @click="filters['status'].value = data"
                    >
                        <div class="flex items-center gap-2">
                            <div :class="[
                                    data === 'active' ? 'bg-green-500' : 'bg-gray-600', 
                                    'w-2 h-2 rounded-full'
                                ]"
                            ></div>
                            <div class="text-xs font-bold">
                                {{ $t(`public.${data}`) }}
                            </div>
                        </div>
                    </Button>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <Button
                    type="button"
                    outlined
                    class="w-full"
                    @click="clearAll"
                >
                    {{ $t('public.clear_all') }}
                </Button>
                <Button
                    type="button"
                    class="w-full"
                    @click="applyFilter"
                >
                    {{ $t('public.apply') }}
                </Button>
            </div>
        </div>
    </Popover>

    <ConfirmationDialog ref="updateStatusConfirm" />
</template>