<script setup>
import {
    Card,
    DataTable,
    Column,
    IconField,
    InputIcon,
    InputText,
    Button,
    Tag,
    ProgressSpinner,
    Popover,
    ToggleSwitch,
    Avatar
} from 'primevue';
import {FilterMatchMode} from "@primevue/core/api";
import { usePage } from '@inertiajs/vue3';
import { ref, watch, defineProps, watchEffect, onMounted } from 'vue';
import { debounce } from 'lodash';
import { IconSearch, IconAdjustments, IconXboxX } from '@tabler/icons-vue';
import Empty from '@/Components/Empty.vue';
import { useLangObserver } from '@/Composables/localeObserver';
import NotificationTableAction from "@/Pages/PushNotification/Partials/NotificationTableAction.vue";
import dayjs from "dayjs";
import {generalFormat} from "@/Composables/format.js";

const props = defineProps({
    pointLogsCount: Number,
});

const { formatNameLabel, formatAmount } = generalFormat();

const isLoading = ref(false);
const dt = ref(null);
const pointHistory = ref([]);
const totalRecords = ref(0);
const first = ref(0);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    type: { value: null, matchMode: FilterMatchMode.EQUALS },
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

            const url = route('point.getPointHistoryData', params);
            const response = await fetch(url);
            const results = await response.json();

            pointHistory.value = results?.data?.data;

            totalRecords.value = results?.data?.total;
            isLoading.value = false;

        }, 100);
    }  catch (e) {
        pointHistory.value = [];
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

//filter type
const types = [
    {label: 'receive', value: 'point_in'},
    {label: 'spend', value: 'point_out'}
];

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

    if(props.pointLogsCount !== 0) {
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
    filters.value['type'].value = null;
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
                :placeholder="$t('public.search')"
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
                        {{ $t('public.list_of_point_history') }}
                    </div>
                </div>
            </div>
        </template>
        <template #content>
            <DataTable
                :value="pointHistory"
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
                :globalFilterFields="['name']"
            >
                <template #empty>
                    <div v-if="pointHistory.length === 0 || pointLogsCount === 0">
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

                <template v-if="pointHistory?.length > 0">
                    <Column
                        field="created_at"
                        class="text-nowrap"
                        sortable
                        :header="$t('public.date')"
                    >
                        <template #body="{ data }">
                            <div class="text-sm">
                                {{ dayjs(data.created_at).format('DD/MM/YYYY h:mm A') }}
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="full_name"
                        :header="$t('public.member')"
                    >
                        <template #body="{ data }">
                            <div class="flex items-center gap-3 self-stretch">
                                <Avatar
                                    v-if="data.user.profile_photo"
                                    :image="data.user.profile_photo"
                                    class="w-10 h-10"
                                />
                                <Avatar
                                    v-else
                                    :label="formatNameLabel(data.user.full_name)"
                                    class="w-10 h-10"
                                />
                                <div class="flex flex-col items-start">
                                    <div class="text-sm font-bold">
                                        {{ data.user.full_name }}
                                    </div>
                                    <div class="text-sm text-surface-500">
                                        {{ data.user.phone_number }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="adjust_type"
                        class="text-nowrap"
                        sortable
                        :header="$t('public.type')"
                    >
                        <template #body="{ data }">
                            <Tag
                                :severity="data.adjust_type === 'point_in' ? 'success' : 'danger'"
                                :value="data.adjust_type === 'point_in' ? $t('public.receive') : $t('public.spend')"
                                rounded
                            />
                        </template>
                    </Column>

                    <Column
                        field="earning_point"
                        class="text-nowrap"
                        sortable
                        :header="$t('public.point')"
                    >
                        <template #body="{ data }">
                            <div class="text-sm font-medium">
                                {{ formatAmount(data.earning_point, 0, '') }} PTS
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="remark"
                        class="text-nowrap"
                        :header="$t('public.remark')"
                    >
                        <template #body="{ data }">
                            {{ data.remark ?? '-' }}
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
                    {{ $t('public.visibility') }}
                </div>
                <div class="w-full flex justify-start items-start content-start gap-2">
                    <Button
                        v-for="data in types"
                        type="button"
                        severity="secondary"
                        rounded
                        :class="[{'bg-surface-300' : filters['type'].value === data.value},
                                'bg-white']"
                        size="small"
                        @click="filters['type'].value = data.value"
                    >
                        <div class="flex items-center gap-2">
                            <div :class="[
                                    data.value === 'point_in' ? 'bg-green-500' : 'bg-red-500',
                                    'w-2 h-2 rounded-full'
                                ]"
                            ></div>
                            <div class="text-xs font-bold">
                                {{ $t(`public.${data.label}`) }}
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
