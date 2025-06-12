<script setup>
import { Dialog, IconField, InputIcon, InputText, Button, DataView } from 'primevue';
import { IconSearch, IconXboxX, IconPlus } from '@tabler/icons-vue';
import { ref, watch, onMounted, watchEffect } from 'vue';
import {generalFormat} from "@/Composables/format.js";
import {useLangObserver} from "@/Composables/localeObserver.js";
import { debounce } from 'lodash';
import {FilterMatchMode} from "@primevue/core/api";
import { usePage } from '@inertiajs/vue3';
import CreateModifierItemModal from '@/Pages/ModifierGroup/Partials/CreateModifierItemModal.vue';

const props = defineProps({
    visible: Boolean,
});

const { formatAmount } = generalFormat();
const { locale } = useLangObserver();

const show = ref(false);
const createModalVisible = ref(false);
const isLoading = ref(false);
const dv = ref(null);
const fetchedProduct = ref([]);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const lazyParams = ref({});

const loadLazyData = (event) => {
    isLoading.value = true;

    lazyParams.value.filters = filters.value;
    try {
        setTimeout(async () => {
            const params = {
                include: [],
                lazyEvent: JSON.stringify(lazyParams.value)
            };

            const url = route('product.fetch', params);
            const response = await fetch(url);
            const results = await response.json();

            fetchedProduct.value = results?.data?.data;
            isLoading.value = false;

        }, 100);
    }  catch (e) {
        fetchedProduct.value = [];
        isLoading.value = false;
    }
};

const onPage = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};
const onFilter = (event) => {
    lazyParams.value.filters = filters.value ;
    loadLazyData(event);
};

onMounted(() => {
    lazyParams.value = {
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

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

watchEffect(() => {
    if (usePage().props.toast !== null) {
        loadLazyData();
    }
});

watch(() => props.visible, (val) => {
    show.value = val;
});

</script>

<template>
    <Dialog
        v-model:visible="show"
        modal
        class="dialog-sm"
    >
        <template #header>
            <div class="text-lg font-bold">
                {{ $t('public.select_modifier_item') }}
            </div>
        </template>
        
        <DataView
            :value="fetchedProduct"
            class="w-full"
            :lazy="true"
            v-model:filters="filters"
            ref="dv"
            dataKey="id"
            @page="onPage($event)"
            @filter="onFilter($event)"
            :globalFilterFields="['name']"
        >
            <template #header>
                <div class="px-5 py-2 flex justify-between items-center self-stretch">
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
                    <Button
                        type="button"
                        severity="contrast"
                        :label="$t('public.create')"
                        @click="createModalVisible = true"
                    >
                        <template #icon>
                            <IconPlus :size="20" />
                        </template>
                    </Button>
                </div>
            </template>

            <template #list="slotProps">
                <div class="flex flex-col items-start self-stretch">
                    <div class="py-4 px-5 flex items-center gap-4 border-b border-solid border-slate-200">
                        <div class="flex flex-col items-start gap-1">
                            <div class="font-bold">
                                name
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </DataView>
    </Dialog>

    <CreateModifierItemModal :visible="createModalVisible" />
</template>