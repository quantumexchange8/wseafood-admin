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
    DatePicker
} from 'primevue';
import {FilterMatchMode} from "@primevue/core/api";
import { usePage } from '@inertiajs/vue3';
import { ref, watch, defineProps, watchEffect, onMounted } from 'vue';
import { debounce } from 'lodash';
import { IconSearch, IconAdjustments, IconXboxX, IconInfinity } from '@tabler/icons-vue';
import Empty from '@/Components/Empty.vue';
import { useLangObserver } from '@/Composables/localeObserver';
import NotificationTableAction from "@/Pages/PushNotification/Partials/NotificationTableAction.vue";
import dayjs from "dayjs";
import MultiSelectOption from "@/Components/MultiSelectOption.vue";
import {generalFormat} from "@/Composables/format.js";

const props = defineProps({
    vouchersCount: Number,
});

const { locale } = useLangObserver();
const {formatAmount} = generalFormat();

const isLoading = ref(false);
const dt = ref(null);
const vouchers = ref([]);
const totalRecords = ref(0);
const first = ref(0);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
    campaign_period: { value: null, matchMode: FilterMatchMode.EQUALS },
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

            const url = route('voucher.getVoucherListingData', params);
            const response = await fetch(url);
            const results = await response.json();

            vouchers.value = results?.data?.data;

            totalRecords.value = results?.data?.total;
            isLoading.value = false;

        }, 100);
    }  catch (e) {
        vouchers.value = [];
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
const status = ref(['active', 'inactive', 'schedule', 'ended', 'fully_claimed']);

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

    if(props.vouchersCount !== 0) {
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
    filters.value['campaign_period'].value = null;
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

const getSeverity = (status) => {
    switch (status) {
        case 'active':
            return 'success';

        case 'schedule':
            return 'info';

        case 'ended':
            return 'secondary';

        case 'fully_claimed':
            return 'danger';

        case 'inactive':
            return 'warning';
    }
}

const getStatusColor = (status) => {
    switch (status) {
        case 'active':
            return 'bg-green-500';
        case 'schedule':
            return 'bg-blue-500';
        case 'ended':
            return 'bg-surface-500';
        case 'fully_claimed':
            return 'bg-red-500';
        case 'inactive':
            return 'bg-yellow-500';
        default:
            return 'bg-surface-600';
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
                        {{ $t('public.list_of_voucher') }}
                    </div>
                    <Tag rounded>
                        <span>{{ vouchersCount }} {{ $t('public.voucher') }}</span>
                    </Tag>
                </div>
            </div>
        </template>
        <template #content>
            <DataTable
                :value="vouchers"
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
                    <div v-if="vouchers.length === 0 || vouchersCount === 0">
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

                <template v-if="vouchers?.length > 0">
                    <Column
                        field="voucher_name"
                        :header="$t('public.voucher_and_code')"
                    >
                        <template #body="{ data }">
                            <div class="flex flex-col text-sm">
                                <div class="font-bold">
                                    {{ data.voucher_name }}
                                </div>
                                <div class="text-surface-500 dark:text-surface-400">
                                    {{ data.voucher_code }}
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="voucher_claimed"
                        :header="$t('public.claimed')"
                    >
                        <template #body="{ data }">
                            <div class="flex gap-1 items-center">
                                {{ data.redeemed_count }}
                                <span>/</span>
                                <div v-if="data.claim_limit === 'limited'">
                                    {{ formatAmount(data.voucher_limit, 0, '') }}
                                </div>
                                <div v-else>
                                    <IconInfinity size="24" stroke-width="1.25" />
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="voucher_used"
                        :header="$t('public.used')"
                    >
                        <template #body="{ data }">
                            {{ data.used_count }}
                        </template>
                    </Column>

                    <Column
                        field="campaign_period"
                        :header="$t('public.campaign_period')"
                    >
                        <template #body="{ data }">
                            <div v-if="data.campaign_period">
                                {{ dayjs(data.validities[0].start_date).format('DD/MM/YYYY') }} - {{ dayjs(data.validities[0].end_date).format('DD/MM/YYYY') }}
                            </div>
                            <div v-else>
                                {{ $t('public.no_campaign_period') }}
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="status"
                        :header="$t('public.status')"
                    >
                        <template #body="{ data }">
                            <Tag
                                :value="$t(`public.${data.status}`)"
                                :severity="getSeverity(data.status)"
                                rounded
                                class="text-xs"
                            />
                        </template>
                    </Column>



<!--                    <Column-->
<!--                        field="action"-->
<!--                        class="w-[100px]"-->
<!--                        :header="$t('public.action')"-->
<!--                    >-->
<!--                        <template #body="{ data }">-->
<!--                            <NotificationTableAction-->
<!--                                :notification="data"-->
<!--                            />-->
<!--                        </template>-->
<!--                    </Column>-->
                </template>
            </DataTable>
        </template>
    </Card>

    <Popover ref="op">
        <div class="flex flex-col gap-6 w-60">
            <div class="flex flex-col gap-4 items-center self-stretch">
                <div class="flex self-stretch text-xs text-surface-950 dark:text-white font-semibold">
                    {{ $t('public.campaign_period') }}
                </div>
                <div class="w-full flex flex-wrap gap-2">
                    <DatePicker
                        v-model="filters['campaign_period'].value"
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
                <div class="w-full flex flex-wrap gap-2">
                    <MultiSelectOption
                        v-model="filters['status'].value"
                        :options="status"
                        multiple
                    >
                        <template #option="{ option, selected }">
                            <div class="flex items-center gap-2">
                                <div
                                    :class="[
                                    getStatusColor(option),
                                    'w-2 h-2 rounded-full'
                                ]"
                                ></div>
                                <div class="text-xs font-bold">
                                    {{ $t(`public.${option}`) }}
                                </div>
                            </div>
                        </template>
                    </MultiSelectOption>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <Button
                    type="button"
                    outlined
                    class="w-full font-bold"
                    @click="clearAll"
                    size="small"
                >
                    {{ $t('public.clear_all') }}
                </Button>
                <Button
                    type="button"
                    class="w-full font-bold"
                    @click="applyFilter"
                    size="small"
                >
                    {{ $t('public.apply') }}
                </Button>
            </div>
        </div>
    </Popover>
</template>
