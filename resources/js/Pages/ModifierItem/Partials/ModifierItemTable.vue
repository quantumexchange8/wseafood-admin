<script setup>
import { Card, DataTable, Column, IconField, InputIcon, InputText, Button, Tag, ProgressSpinner, Popover } from 'primevue';
import {FilterMatchMode} from "@primevue/core/api";
import { usePage } from '@inertiajs/vue3';
import { ref, watch, defineProps, watchEffect, onMounted } from 'vue';
import { debounce } from 'lodash';
import { IconSearch, IconXboxX, IconUpload } from '@tabler/icons-vue';
import Empty from '@/Components/Empty.vue';
import {useLangObserver} from "@/Composables/localeObserver.js";
import ModifierItemTableAction from '@/Pages/ModifierItem/Partials/ModifierItemTableAction.vue';

const props = defineProps({
    category: Object,
    categoryCount: Number,
});

const { locale } = useLangObserver();

const isLoading = ref(false);
const dt = ref(null);
const fetchedItem = ref([]);
const totalRecords = ref(0);
const first = ref(0);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
    name: { value: null, matchMode: FilterMatchMode.EQUALS },
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

            const url = route('modifier.item.fetch', params);
            const response = await fetch(url);
            const results = await response.json();

            fetchedItem.value = results?.data?.data;

            totalRecords.value = results?.data?.total;
            isLoading.value = false;

        }, 100);
    }  catch (e) {
        fetchedItem.value = [];
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

onMounted(() => {
    lazyParams.value = {
        first: dt.value.first,
        rows: dt.value.rows,
        sortField: null,
        sortOrder: null,
        filters: filters.value
    };

    if(props.categoryCount !== 0) {
        loadLazyData();
    }
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
    </div>

    <Card class="w-full">
        <template #title>
            <div class="p-4 flex justify-between items-center gap-2 self-stretch">
                <div class="flex items-center gap-4">
                    <div class="text-lg font-bold">
                        {{ $t('public.list_of_modifier_item') }}
                    </div>
                    <Tag rounded>
                        <span>{{ totalRecords }} {{ $t('public.modifier_item') }}</span>
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
                :value="fetchedItem"
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
                    <div v-if="fetchedItem.length === 0 || categoryCount === 0">
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

                <template v-if="fetchedItem?.length > 0">
                    <Column
                        field="name"
                        sortable
                        :header="$t('public.modifier_item_name')"
                    >
                        <template #body="{ data }">
                            <div class="text-sm font-bold">
                                {{ JSON.parse(data.name)[locale] ?? JSON.parse(data.name)['en'] }}
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="number_group"
                        class="w-[100px] text-nowrap"
                        :header="$t('public.number_modifier_group')"
                    >
                        <template #body="{ data }">
                            <div class="text-sm">
                                {{ data.group_count }}
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="action"
                        class="w-[100px]"
                        :header="$t('public.action')"
                    >
                        <template #body="{ data }">
                            <ModifierItemTableAction :modifierItem="data" />
                        </template>
                    </Column>
                </template>
            </DataTable>
        </template>
    </Card>
</template>