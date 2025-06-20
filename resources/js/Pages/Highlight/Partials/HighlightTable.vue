<script setup>
import { Card, DataTable, Column, IconField, InputIcon, InputText, Button, Tag, ProgressSpinner, Popover, ToggleSwitch, Avatar, SelectButton } from 'primevue';
import {FilterMatchMode} from "@primevue/core/api";
import { router, usePage } from '@inertiajs/vue3';
import { ref, watch, defineProps, watchEffect, onMounted } from 'vue';
import { debounce } from 'lodash';
import { IconSearch, IconAdjustments, IconXboxX, IconPencil, IconTrash, IconUpload } from '@tabler/icons-vue';
import Empty from '@/Components/Empty.vue';
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';

const props = defineProps({
    highlight: Object,
    highlightCount: Number,
});

const isLoading = ref(false);
const dt = ref(null);
const fetchedHighlight = ref([]);
const totalRecords = ref(0);
const first = ref(0);
const updateStatusConfirm = ref(null);
const normalState = ref(true);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
    name: { value: null, matchMode: FilterMatchMode.EQUALS },
    popup: { value: null, matchMode: FilterMatchMode.EQUALS },
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

            const url = route('highlight.fetch', params);
            const response = await fetch(url);
            const results = await response.json();

            fetchedHighlight.value = results?.data?.data;

            totalRecords.value = results?.data?.total;
            isLoading.value = false;

        }, 100);
    }  catch (e) {
        fetchedHighlight.value = [];
        totalRecords.value = 0;
        isLoading.value = false;
    }
};

const onPage = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};
const onSort = (event) => {
    normalState.value = false;
    if(event.sortOrder === null) {
        normalState.value = true;
    }
    
    lazyParams.value = event;
    loadLazyData(event);
};
const onFilter = (event) => {
    lazyParams.value.filters = filters.value ;
    loadLazyData(event);
};

//filter status
const status = ref(['active', 'inactive']);
const popup = ref([1, 0]);

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

    if(props.highlightCount !== 0) {
        loadLazyData();
    }
})

watch(
    filters.value['global'],
    debounce(() => {
        normalState.value = false;
        if(filters.value['global'].value === null) {
            normalState.value = true;
        }
        loadLazyData();
    }, 300)
);

const clearAll = () => {
    filters.value['global'].value = null;
    filters.value['status'].value = null;
    filters.value['popup'].value = null;
};

const clearFilterGlobal = () => {
    normalState.value = true;
    filters.value['global'].value = null;
}

watchEffect(() => {
    if (usePage().props.toast !== null) {
        loadLazyData();
    }
});

const updateStatus = (highlight) => {
    if(updateStatusConfirm.value) {
        updateStatusConfirm.value.changeStatus(highlight.id, highlight.title, highlight.status, 'highlight.updateStatus');
    } else {
        console.error("Update Status Confirmation is not available");
    }
};

const updatePopup = (highlight) => {
    if(updateStatusConfirm.value) {
        updateStatusConfirm.value.changePopup(highlight.id, highlight.title, highlight.can_popup, 'highlight.updatePopup');
    } else {
        console.error("Update Status Confirmation is not available");
    }
};

const applyFilter = () => {
    normalState.value = false;
    if(!filters.value['global'].value && !filters.value['status'].value && !filters.value['popup'].value) {
        normalState.value = true;
    }
    loadLazyData();
};

const reorderItem = (event) => {
    router.post(route('highlight.reorder'), event.value);
}

const deleteHighlight = (highlight) => {
    if(updateStatusConfirm.value) {
        updateStatusConfirm.value.deleteItem(highlight.id, highlight.title, 'highlight.destroy');
    } else {
        console.error("Update Status Confirmation is not available");
    }
}

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
                        {{ $t('public.list_of_highlight') }}
                    </div>
                    <Tag rounded>
                        <span>{{ totalRecords }} {{ $t('public.highlight') }}</span>
                    </Tag>
                </div>
            </div>
        </template>
        <template #content>
            <DataTable
                :value="fetchedHighlight"
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
                @rowReorder="reorderItem"
                @page="onPage($event)"
                @sort="onSort($event)"
                @filter="onFilter($event)"
                :globalFilterFields="['name', 'status']"
            >
                <template #empty>
                    <div v-if="fetchedHighlight.length === 0 || highlightCount === 0">
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

                <template v-if="fetchedHighlight?.length > 0">
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
                        field="title"
                        sortable
                    >
                        <template #header>
                            <div class="text-xs font-bold text-nowrap">
                                {{ $t('public.title') }}
                            </div>
                        </template>
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <div class="text-sm font-bold">
                                    {{ data.title }}
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="can_popup"
                        class="w-[100px]"
                        sortable
                    >
                        <template #header>
                            <div class="text-xs font-bold text-nowrap">
                                {{ $t('public.popup_highlight') }}
                            </div>
                        </template>
                        <template #body="{ data }">
                            <div class="w-full flex justify-center">
                                <ToggleSwitch
                                    :model-value="data.can_popup"
                                    :true-value="1"
                                    :false-value="0"
                                    @click="updatePopup(data)"
                                    readonly
                                />
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
                                    @click="router.visit(route('highlight.edit', data.id))"
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
                                    @click="deleteHighlight(data)"
                                >
                                    <template #icon>
                                        <IconTrash :size="16" stroke-width="1.5" class="text-red-500"/>
                                    </template>
                                </Button>
                            </div>
                        </template>
                    </Column>
                    <Column 
                        v-if="normalState"
                        rowReorder 
                        class="w-12" 
                        :reorderableColumn="false" 
                    />
                </template>
            </DataTable>
        </template>
    </Card>

    <Popover ref="op">
        <div class="flex flex-col gap-6 w-60">
            <div class="flex flex-col gap-4 items-center self-stretch">
                <div class="flex self-stretch text-xs text-surface-950 dark:text-white font-semibold">
                    {{ $t('public.visibility') }}
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

            <div class="flex flex-col gap-4 items-center self-stretch">
                <div class="flex self-stretch text-xs text-surface-950 dark:text-white font-semibold">
                    {{ $t('public.popup_highlight') }}
                </div>
                <div class="w-full flex justify-start items-start content-start gap-2">
                    <Button
                        v-for="data in popup"
                        type="button"
                        severity="secondary"
                        rounded
                        :class="{'bg-gray-300' : filters['popup'].value === data},
                                'bg-white'"
                        size="small"
                        @click="filters['popup'].value = data"
                    >
                        <div class="flex items-center gap-2">
                            <div :class="[
                                    data === 1 ? 'bg-green-500' : 'bg-gray-600', 
                                    'w-2 h-2 rounded-full'
                                ]"
                            ></div>
                            <div class="text-xs font-bold">
                                {{ data === 1 ? $t('public.on') : $t('public.off') }}
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