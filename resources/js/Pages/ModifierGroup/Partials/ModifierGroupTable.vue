<script setup>
import { Card, DataTable, Column, IconField, InputIcon, InputText, Button, Tag, ProgressSpinner, Popover, Select, ToggleSwitch, Avatar } from 'primevue';
import {FilterMatchMode} from "@primevue/core/api";
import { usePage } from '@inertiajs/vue3';
import { ref, watch, defineProps, watchEffect, onMounted } from 'vue';
import { debounce } from 'lodash';
import { IconSearch, IconAdjustments, IconXboxX, IconPencil, IconTrash, IconUpload } from '@tabler/icons-vue';
import Empty from '@/Components/Empty.vue';
import {useLangObserver} from "@/Composables/localeObserver.js";
import UpdateStatusConfirmation from '@/Pages/Product/Partials/UpdateStatusConfirmation.vue';
// import DeleteConfirmation from '@/Components/DeleteConfirmation.vue';

const props = defineProps({
    category: Object,
});

const { locale } = useLangObserver();

const isLoading = ref(false);
const dt = ref(null);
const fetchedCategory = ref([]);
const totalRecords = ref(0);
const first = ref(0);
const updateStatusConfirm = ref(null);
const deleteConfirm = ref(null);

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

            const url = route('modifier.group.fetch', params);
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

const updateStatus = (group) => {
    if(updateStatusConfirm.value) {
        updateStatusConfirm.value.changeStatus(group);
    } else {
        console.error("Update Status Confirmation is not available");
    }
};

// const deleteGroup = (group) => {
//     if(deleteConfirm.value) {
//         deleteConfirm.value.deleteItem(group.id, group.group_name, 'modifier.group.destroy')
//     }
// }

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
                    <div v-if="fetchedCategory.length === 0">
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
                            <span class="block text-nowrap">
                                {{ $t('public.visibility') }}
                            </span>
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
                        field="id"
                        class="w-[100px] text-nowrap"
                        sortable
                    >
                        <template #header>
                            <span class="block text-nowrap">
                                {{ $t('public.group_id') }}
                            </span>
                        </template>
                        <template #body="{ data }">
                            <span>
                                {{ data.id }}
                            </span>
                        </template>
                    </Column>

                    <Column
                        field="group_name"
                        class="w-full"
                        sortable
                    >
                        <template #header>
                            <span class="block text-nowrap">
                                {{ $t('public.group_name_and_item') }}
                            </span>
                        </template>
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
                    >
                        <template #header>
                            <span class="block text-nowrap">
                                {{ $t('public.type') }}
                            </span>
                        </template>
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
                    >
                        <template #header>
                            <span class="block text-nowrap">
                                {{ $t('public.linked_item') }}
                            </span>
                        </template>
                        <template #body="{ data }">
                            <span>
                                {{ data.product_count }}
                            </span>
                        </template>
                    </Column>

                    <Column
                        field="action"
                        class="w-[100px]"
                    >
                        <template #header>
                            <span class="block text-nowrap">
                                {{ $t('public.action') }}
                            </span>
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
                                    @click="deleteGroup(data)"
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
            <!-- Filter status -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-surface-950 dark:text-white font-semibold">
                    {{ $t('public.filter_by_status') }}
                </div>
                <Select
                    v-model="filters['status'].value"
                    :options="status"
                    :placeholder="$t('public.select_status')"
                    class="w-full"
                    showClear
                >
                    <template #value="slotProps">
                        <div v-if="slotProps.value" class="flex items-center">
                            {{ $t(`public.${slotProps.value}`) }}
                        </div>
                        <span v-else>{{ slotProps.placeholder }}</span>
                    </template>

                    <template #option="slotProps">
                        <div>
                            {{ $t(`public.${slotProps.option}`) }}
                        </div>
                    </template>
                </Select>
            </div>

            <Button
                type="button"
                outlined
                class="w-full"
                @click="clearAll"
            >
                {{ $t('public.clear_all') }}
            </Button>
        </div>
    </Popover>

    <UpdateStatusConfirmation ref="updateStatusConfirm" item="modifier_group"/>
</template>