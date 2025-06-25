<script setup>
import { Card, DataTable, Column, IconField, InputIcon, InputText, Button, Tag, ProgressSpinner, Popover, DatePicker, Slider, Avatar } from 'primevue';
import {FilterMatchMode} from "@primevue/core/api";
import { usePage } from '@inertiajs/vue3';
import { ref, watch, defineProps, watchEffect, onMounted } from 'vue';
import { debounce } from 'lodash';
import { IconSearch, IconAdjustments, IconXboxX, IconUpload } from '@tabler/icons-vue';
import Empty from '@/Components/Empty.vue';
import {generalFormat} from "@/Composables/format.js";
import MemberTableAction from '@/Pages/Member/Partials/MemberTableAction.vue';

const props = defineProps({
    member: Object,
});

const { formatNameLabel, formatDateTime } = generalFormat();

const isLoading = ref(false);
const dt = ref(null);
const fetchedMember = ref([]);
const totalRecords = ref(0);
const first = ref(0);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
    name: { value: null, matchMode: FilterMatchMode.EQUALS },
    pointRange: {value: null, matchMode: FilterMatchMode.BETWEEN},
    date: {value: null, matchMode: FilterMatchMode.BETWEEN},
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

            const url = route('member.fetch', params);
            const response = await fetch(url);
            const results = await response.json();

            fetchedMember.value = results?.data?.data;

            totalRecords.value = results?.data?.total;
            isLoading.value = false;

        }, 100);
    }  catch (e) {
        fetchedMember.value = [];
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

const applyFilter = () => {
    loadLazyData();
};

const clearAll = () => {
    filters.value['global'].value = null;
    filters.value['status'].value = null;
    filters.value['pointRange'].value = [0, 2000];
    filters.value['date'].value = null;
};

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

watchEffect(() => {
    if (usePage().props.toast !== null) {
        loadLazyData();
    }
});

const getSeverity = (status) => {
    switch (status) {
        case 'active':
            return 'success';

        case 'inactive':
            return 'secondary';
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
            class="flex gap-2 items-center w-full md:w-fit font-bold"
            severity="contrast"
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
                        {{ $t('public.list_of_member') }}
                    </div>
                    <Tag rounded>
                        <span>{{ totalRecords }} {{ $t('public.member') }}</span>
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
                :value="fetchedMember"
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
                    <div v-if="fetchedMember.length === 0">
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

                <template v-if="fetchedMember?.length > 0">
                    <Column
                        field="id"
                        class="w-[100px]"
                        sortable
                        :header="$t('public.id')"
                    >
                        <template #body="{ data }">
                            <div class="text-sm">
                                {{ data.id }}
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="full_name"
                        sortable
                        :header="$t('public.member')"
                    >
                        <template #body="{ data }">
                            <div class="flex items-center gap-3 self-stretch">
                                <Avatar
                                    v-if="data.profile_photo"
                                    :image="data.profile_photo"
                                    class="w-10 h-10"
                                />
                                <Avatar
                                    v-else
                                    :label="formatNameLabel(data.full_name)"
                                    class="w-10 h-10"
                                    size="large"
                                />
                                <div class="flex flex-col gap-1 items-start">
                                    <div class="text-sm font-bold">
                                        {{ data.full_name }}
                                    </div>
                                    <div class="text-sm">
                                        {{ data.phone }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="point"
                        class="w-[100px] text-nowrap"
                        sortable
                        :header="$t('public.point')"
                    >
                        <template #body="{ data }">
                            <div class="text-sm">
                                {{ data.point }} PTS
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="created_at"
                        class="w-[100px] text-nowrap"
                        sortable
                        :header="$t('public.joined_on')"
                    >
                        <template #body="{ data }">
                            <div class="text-sm">
                                {{ formatDateTime(data.created_at, false) }}
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="status"
                        class="w-[100px] text-nowrap"
                        sortable
                        :header="$t('public.status')"
                    >
                        <template #body="{ data }">
                            <Tag 
                                :value="$t(`public.${data.status}`)" 
                                :severity="getSeverity(data.status)"
                                rounded
                            />
                        </template>
                    </Column>

                    <Column
                        field="action"
                        class="w-[100px]"
                        :header="$t('public.action')"
                    >
                        <template #body="{ data }">
                            <MemberTableAction :member="data" />
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
                    {{ $t('public.point_accumulated') }}
                </div>
                <div class="w-full flex flex-col justify-center items-center gap-3">
                    <Slider
                        v-model="filters['pointRange'].value"
                        range
                        :min="0"
                        :max="2000"
                        :step="10"
                        class="w-[90%]"
                    />
                    <div class="w-full flex justify-between items-center">
                        <div class="text-xs">
                            0 PTS
                        </div>
                        <div class="text-xs">
                            2000 PTS
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 items-center self-stretch">
                <div class="flex self-stretch text-xs text-surface-950 dark:text-white font-semibold">
                    {{ $t('public.joined_date') }}
                </div>
                <div class="w-full flex items-center">
                    <DatePicker
                        v-model="filters['date'].value"
                        selectionMode="range"
                        :manualInput="false"
                        dateFormat="dd/mm/yy"
                        placeholder="dd/mm/yyyy - dd/mm/yyyy"
                        class="w-full"
                    />
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