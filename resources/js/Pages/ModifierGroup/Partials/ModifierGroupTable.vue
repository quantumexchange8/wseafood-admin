<script setup>
import { Card, DataTable, Column, IconField, InputIcon, InputText, Button, Tag, ProgressSpinner, Popover, Slider } from 'primevue';
import {FilterMatchMode} from "@primevue/core/api";
import { usePage } from '@inertiajs/vue3';
import { ref, watch, watchEffect, onMounted } from 'vue';
import { debounce } from 'lodash';
import { IconSearch, IconAdjustments, IconXboxX, IconUpload } from '@tabler/icons-vue';
import Empty from '@/Components/Empty.vue';
import {useLangObserver} from "@/Composables/localeObserver.js";
import StatusSwitch from '@/Components/StatusSwitch.vue';
import ModifierGroupTableAction from './ModifierGroupTableAction.vue';

const props = defineProps({
    groupCount: Number,
});

const { locale } = useLangObserver();

const isLoading = ref(false);
const dt = ref(null);
const fetchedGroup = ref([]);
const totalRecords = ref(0);
const first = ref(0);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
    name: { value: null, matchMode: FilterMatchMode.EQUALS },
    itemRange: { value: null, matchMode: FilterMatchMode.BETWEEN },
    groupType: { value: null, matchMode: FilterMatchMode.EQUALS },
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

            const url = route('modifier.group.fetch', params);
            const response = await fetch(url);
            const results = await response.json();

            fetchedGroup.value = results?.data?.data;

            totalRecords.value = results?.data?.total;
            isLoading.value = false;

        }, 100);
    }  catch (e) {
        fetchedGroup.value = [];
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

const groupTypes = ref(['required', 'optional']);

//filter toggle
const op = ref();
const toggle = (event) => {
    op.value.toggle(event);
};

onMounted(() => {
    lazyParams.value = {
        first: dt.value.first,
        rows: dt.value.rows,
        sortField: null,
        sortOrder: null,
        filters: filters.value
    };

    if(props.groupCount !== 0) {
        loadLazyData();
    }
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
    filters.value['itemRange'].value = null;
    filters.value['groupType'].value = null;
};

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

watchEffect(() => {
    if (usePage().props.toast !== null) {
        loadLazyData();
    }
});

const applyFilter = () => {
    loadLazyData();
};

</script>
<template>
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
            class="flex gap-2 items-center w-full md:w-fit font-bold"
            severity="contrast"
            @click="toggle"
        >
            <IconAdjustments :size="20" stroke-width="1.5"/>
            {{ $t('public.filter') }}
        </Button>
    </div>

    <Card class="w-full">
        <template #title>
            <div class="p-4 flex justify-between items-center gap-2 self-stretch">
                <div class="flex items-center gap-4">
                    <div class="text-lg font-bold">
                        {{ $t('public.list_of_modifier_group') }}
                    </div>
                    <Tag rounded>
                        <span>{{ totalRecords }} {{ $t('public.modifier_group') }}</span>
                    </Tag>
                </div>
                <Button
                    type="button"
                    severity="secondary"
                    outlined
                    size="small"
                >
                    <IconUpload :size="16" stroke-width="1.5"/>
                    {{ $t('public.export') }}
                </Button>
            </div>
        </template>
        <template #content>
            <DataTable
                :value="fetchedGroup"
                lazy
                paginator
                removableSort
                :rows="10"
                :rowsPerPageOptions="[10, 20, 50, 100]"
                :first="first"
                paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                :currentPageReportTemplate="$t('public.paginator_caption')"
                v-model:filters="filters"
                ref="dt"
                dataKey="id"
                :loading="isLoading"
                :totalRecords="totalRecords"
                @page="onPage($event)"
                @sort="onSort($event)"
                @filter="onFilter($event)"
                :globalFilterFields="['name', 'status']"
            >
                <template #empty>
                    <div v-if="fetchedGroup.length === 0">
                        <Empty
                            :title="$t('public.no_data_found')"
                        />
                    </div>
                </template>

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

                <template v-if="fetchedGroup?.length > 0">
                    <Column
                        field="status"
                        class="w-[100px]"
                        sortable
                        :header="$t('public.visibility')"
                    >
                        <template #body="{ data }">
                            <StatusSwitch :data="data" path="modifier.group.updateStatus" />
                        </template>
                    </Column>

                    <Column
                        field="id"
                        class="w-[100px] text-nowrap"
                        sortable
                        :header="$t('public.group_id')"
                    >
                        <template #body="{ data }">
                            <div class="text-sm">
                                {{ data.id }}
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="group_name"
                        class="w-full"
                        sortable
                        :header="$t('public.group_name_and_item')"
                    >
                        <template #body="{ data }">
                            <div class="flex flex-col items-start gap-1">
                                <div class="text-sm font-bold">
                                    {{ data.group_name }} ({{ data.item_count }})
                                </div>
                                <div class="text-sm">
                                    <template v-for="(item, index) in data.items">
                                        {{ JSON.parse(item.modifier_item_name)[locale] ?? JSON.parse(item.modifier_item_name)['en'] }}{{ index+1 === data.items.length ? '' : ', ' }}
                                    </template>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="group_type"
                        class="text-nowrap w-[140px]"
                        sortable
                        :header="$t('public.type')"
                    >
                        <template #body="{ data }">
                            <div class="flex flex-col justify-center items-start self-stretch">
                                <div class="text-sm font-bold">
                                    {{ $t(`public.${data.group_type}`) }}
                                </div>
                                <div class="text-sm">
                                    min. {{ data.min_selection }} - max. {{ data.max_selection??'∞' }}
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="number_product"
                        class="w-[100px] text-nowrap"
                        :header="$t('public.linked_item')"
                    >
                        <template #body="{ data }">
                            <div class="text-sm">
                                {{ data.product_count }}
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="action"
                        class="w-[100px]"
                        :header="$t('public.action')"
                    >
                        <template #body="{ data }">
                            <ModifierGroupTableAction :modifierGroup="data" />
                        </template>
                    </Column>
                </template>
            </DataTable>
        </template>
    </Card>

    <Popover ref="op">
        <div class="flex flex-col gap-6 w-60">
            <div class="flex flex-col gap-4 items-center self-stretch">
                <div class="flex self-stretch text-xs text-surface-950 dark:text-white font-semibold">
                    {{ $t('public.number_modifier_item') }}
                </div>
                <div class="w-full flex flex-col justify-center items-center gap-3">
                    <Slider
                        v-model="filters['itemRange'].value"
                        range
                        :min="0"
                        :max="10"
                        :step="1"
                        class="w-[90%]"
                    />
                    <div class="w-[95%] flex justify-between items-center">
                        <div class="text-xs">
                            0
                        </div>
                        <div class="text-xs">
                            10
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 items-center self-stretch">
                <div class="flex self-stretch text-xs text-surface-950 dark:text-white font-semibold">
                    {{ $t('public.group_type') }}
                </div>
                <div class="w-full flex justify-start items-start content-start gap-2">
                    <Button
                        v-for="data in groupTypes"
                        type="button"
                        severity="secondary"
                        rounded
                        :class="{'bg-gray-300' : filters['groupType'].value === data},
                                'bg-white'"
                        size="small"
                        @click="filters['groupType'].value = data"
                    >
                        <div class="text-xs font-bold">
                            {{ $t(`public.${data}`) }}
                        </div>
                    </Button>
                </div>
            </div>

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
</template>