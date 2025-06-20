<script setup>
import { Card, DataTable, Column, IconField, InputIcon, InputText, Button, Tag, ProgressSpinner, Popover, Select, ToggleSwitch, Avatar, SelectButton } from 'primevue';
import {FilterMatchMode} from "@primevue/core/api";
import { router, usePage } from '@inertiajs/vue3';
import { ref, watch, defineProps, watchEffect, onMounted } from 'vue';
import { debounce } from 'lodash';
import { IconSearch, IconAdjustments, IconXboxX, IconPencil, IconTrash, IconUpload } from '@tabler/icons-vue';
import Empty from '@/Components/Empty.vue';
import {generalFormat} from "@/Composables/format.js";
import {useLangObserver} from "@/Composables/localeObserver.js";
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';

const props = defineProps({
    category: Object,
    categoryCount: Number,
});

const { formatNameLabel } = generalFormat();
const { locale } = useLangObserver();

const isLoading = ref(false);
const dt = ref(null);
const fetchedCategory = ref([]);
const totalRecords = ref(0);
const first = ref(0);
const updateStatusConfirm = ref(null);

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

            const url = route('category.fetch', params);
            const response = await fetch(url);
            const results = await response.json();

            fetchedCategory.value = results?.data?.data;

            totalRecords.value = results?.data?.total;
            isLoading.value = false;

        }, 100);
    }  catch (e) {
        fetchedCategory.value = [];
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

const updateStatus = (category) => {
    if(updateStatusConfirm.value) {
        const category_name = JSON.parse(category.name)[locale] ?? JSON.parse(category.name)['en'];
        updateStatusConfirm.value.changeStatus(category.id, category_name, category.status, 'category.updateStatus');
    } else {
        console.error("Update Status Confirmation is not available");
    }
};

const applyFilter = () => {
    loadLazyData();
};

const deleteCategory = (category) => {
    if(updateStatusConfirm.value) {
        const category_name = JSON.parse(category.name)[locale] ?? JSON.parse(category.name)['en'];
        updateStatusConfirm.value.deleteItem(category.id, category_name, 'category.destroy');
    } else {
        console.error("Delete Confirmation is not available");
    }
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
            class="flex gap-2 items-center w-full md:w-fit font-bold !bg-white"
            severity="secondary"
            outlined
            @click="toggle"
        >
            <IconAdjustments :size="16" stroke-width="1.5"/>
            {{ $t('public.filter') }}
        </Button>
    </div>

    <Card class="w-full">
        <template #title>
            <div class="p-4 flex justify-between items-center gap-2 self-stretch">
                <div class="flex items-center gap-4">
                    <div class="text-lg font-bold">
                        {{ $t('public.list_of_category') }}
                    </div>
                    <Tag rounded>
                        <span>{{ totalRecords }} {{ $t('public.categories') }}</span>
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
                :value="fetchedCategory"
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
                    <div v-if="fetchedCategory.length === 0 || categoryCount === 0">
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

                <template v-if="fetchedCategory?.length > 0">
                    <Column
                        field="status"
                        class="w-[100px]"
                        sortable
                    >
                        <template #header>
                            <div class="text-xs font-bold text-nowrap">
                                {{ $t('public.visibility') }}
                            </div>
                        </template>
                        <template #body="{ data }">
                            <ToggleSwitch
                                :model-value="data.status === 'active' ? true : false"
                                @click="updateStatus(data)"
                                readonly
                            />
                        </template>
                    </Column>

                    <Column
                        field="name"
                        sortable
                    >
                        <template #header>
                            <div class="text-xs font-bold text-nowrap">
                                {{ $t('public.category_name') }}
                            </div>
                        </template>
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Avatar
                                    v-if="data.category_thumbnail"
                                    :image="data.category_thumbnail"
                                    class="w-10 h-10"
                                />
                                <Avatar
                                    v-else
                                    :label="formatNameLabel(JSON.parse(data.name)[locale] ?? JSON.parse(data.name)['en'])"
                                    class="w-10 h-10 text-sm"
                                    size="large"
                                />
                                <div class="text-sm font-bold">
                                    {{ JSON.parse(data.name)[locale] ?? JSON.parse(data.name)['en'] }}
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="number_product"
                        class="w-[100px] text-nowrap"
                    >
                        <template #header>
                            <div class="text-xs font-bold text-nowrap">
                                {{ $t('public.number_of_product') }}
                            </div>
                        </template>
                        <template #body="{ data }">
                            <div class="text-sm">
                                {{ data.product_count }}
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="action"
                        class="w-[100px]"
                    >
                        <template #header>
                            <div class="text-xs font-bold text-nowrap">
                                {{ $t('public.action') }}
                            </div>
                        </template>
                        <template #body="{ data }">
                            <div class="flex items-center gap-3">
                                <Button
                                    type="button"
                                    severity="secondary"
                                    outlined
                                    size="small"
                                    class="rounded-full"
                                    icon="IconPencil"
                                    @click="router.visit(route('category.edit', data.id))"
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
                                    @click="deleteCategory(data)"
                                >
                                    <template #icon>
                                        <IconTrash :size="16" stroke-width="1.5" class="text-red-500"/>
                                    </template>
                                </Button>
                            </div>
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