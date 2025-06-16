<script setup>
import { Dialog, IconField, InputIcon, InputText, Button, DataView, Checkbox, Tree, Avatar } from 'primevue';
import { IconSearch, IconXboxX, IconPlus } from '@tabler/icons-vue';
import { ref, watch, onMounted, watchEffect } from 'vue';
import {useLangObserver} from "@/Composables/localeObserver.js";
import {generalFormat} from "@/Composables/format.js";
import { debounce } from 'lodash';
import {FilterMatchMode} from "@primevue/core/api";
import { usePage } from '@inertiajs/vue3';
import LoadingMask from '@/Pages/ModifierGroup/Partials/LoadingMask.vue';
import Empty from '@/Components/Empty.vue';
import ProductPhoto from '@/Pages/Product/Partials/ProductPhoto.vue';

const props = defineProps({
    visible: Boolean,
    categoryCount: Number,
    updateChecked: Object,
});

const emit = defineEmits(['update:visible', 'update:addItem']);

const { locale } = useLangObserver();
const { formatNameLabel,formatAmount } = generalFormat();

const show = ref(false);
const isLoading = ref(false);
const fetchedItem = ref([]);
const checkedItem = ref();
const selectedkey = ref();

const nodes = ref([
    {
        id: '0',
        label: 'Introduction',
        children: [
            { key: '0-0', label: 'What is Vue.js?', data: 'https://vuejs.org/guide/introduction.html#what-is-vue', type: 'url' },
            { key: '0-1', label: 'Quick Start', data: 'https://vuejs.org/guide/quick-start.html#quick-start', type: 'url' },
            { key: '0-2', label: 'Creating a Vue Application', data: 'https://vuejs.org/guide/essentials/application.html#creating-a-vue-application', type: 'url' },
            { key: '0-3', label: 'Conditional Rendering', data: 'https://vuejs.org/guide/essentials/conditional.html#conditional-rendering', type: 'url' }
        ]
    },
    {
        id: '1',
        label: 'Components In-Depth',
        children: [
            { key: '1-0', label: 'Component Registration', data: 'https://vuejs.org/guide/components/registration.html#component-registration', type: 'url' },
            { key: '1-1', label: 'Props', data: 'https://vuejs.org/guide/components/props.html#props', type: 'url' },
            { key: '1-2', label: 'Components Events', data: 'https://vuejs.org/guide/components/events.html#component-events', type: 'url' },
            { key: '1-3', label: 'Slots', data: 'https://vuejs.org/guide/components/slots.html#slots', type: 'url' }
        ]
    }
]);

if(props.updateChecked) {
    checkedItem.value = props.updateChecked;
}

// const filters = ref({
//     global: { value: null, matchMode: FilterMatchMode.CONTAINS },
//     name: { value: null, matchMode: FilterMatchMode.EQUALS },
// });

// const lazyParams = ref({});

// const loadLazyData = (event) => {
//     isLoading.value = true;

//     lazyParams.value.filters = filters.value;
//     try {
//         setTimeout(async () => {
//             const params = {
//                 sortField: event?.sortField,
//                 sortOrder: event?.sortOrder,
//                 include: [],
//                 lazyEvent: JSON.stringify(lazyParams.value)
//             };

//             const url = route('modifier.categoryProduct.fetch', params);
//             const response = await fetch(url);
//             const results = await response.json();

//             fetchedItem.value = results?.data;
//         console.log(fetchedItem.value);

//             isLoading.value = false;

//         }, 100);
//     }  catch (e) {
//         fetchedItem.value = [];
//         isLoading.value = false;
//     }
// };

// const onPage = (event) => {
//     lazyParams.value = event;
//     loadLazyData(event);
// };
// const onFilter = (event) => {
//     lazyParams.value.filters = filters.value ;
//     loadLazyData(event);
// };

const loadLazyData = async () => {
    try{
        isLoading.value = true
        const response = await axios.get(route('modifier.categoryProduct.fetch'));
        fetchedItem.value = response.data.data;
        console.log(fetchedItem.value);
        
    } catch (e) {
        console.log(e);
        
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    // lazyParams.value = {
    //     sortField: null,
    //     filters: filters.value
    // };

    loadLazyData();
})

// watch(
//     filters.value['global'],
//     debounce(() => {
//         loadLazyData();
//     }, 300)
// );

// const clearFilterGlobal = () => {
//     filters.value['global'].value = null;
// }

watchEffect(() => {
    if (usePage().props.toast !== null) {
        loadLazyData();
    }
});

watch(() => props.visible, (val) => {
    show.value = val;
});

watch(show, (val) => {
    if (val !== props.visible) {
        emit('update:visible', val);
    }
});

watch(() => props.updateChecked, () => {
    checkedItem.value = props.updateChecked;
});

const addItem = () => {
    emit('update:addItem', checkedItem.value);
    show.value = false;
}

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

        <Tree
            :loading="isLoading"
            :value="fetchedItem"
            v-model:selection-keys="selectedkey"
            selectionMode="checkbox"
            :filter="true"
            filterMode="lenient"
            class="px-5 py-4 w-full"
        >
            <template #loadingicon>
                <LoadingMask :itemCount="3" />
            </template>

            <!-- <template #default="slotProps">
                <b>{{ slotProps.node.label }}</b>
            </template>
            <template #url="slotProps">
                <a :href="slotProps.node.data" target="_blank" rel="noopener noreferrer" class="text-surface-700 dark:text-surface-0 hover:text-primary">{{ slotProps.node.label }}</a>
            </template> -->
            <template #default="slotProps">
                <b>{{ slotProps.node.name }}</b>
            </template>
            <template #meal="slotProps">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10">
                        <Avatar
                            v-if="slotProps.node.product_photo"
                            :image="slotProps.node.product_photo"
                            class="w-10 h-10"
                        />
                        <Avatar
                            v-else
                            :label="formatNameLabel(JSON.parse(slotProps.node.name)[locale] ?? JSON.parse(slotProps.node.name)['en'])"
                            class="w-10 h-10"
                        />
                    </div>
                    <div class="flex flex-col items-start gap-1">
                        <div class="flex items-start gap-2">
                            <div class="font-bold">
                                {{ slotProps.node.key }}
                            </div>
                            <div class="font-bold">
                                {{ formatNameLabel(JSON.parse(slotProps.node.name)[locale] ?? JSON.parse(slotProps.node.name)['en']) }}
                            </div>
                        </div>
                        <div>
                            {{ slotProps.node.price }}
                        </div>
                    </div>
                </div>
            </template>
        </Tree>

        <template #footer>
            <Button
                type="button"
                label="Cancel"
                severity="secondary"
                outlined
                @click="show = false"
            />
            <Button
                type="submit"
                label="Add"
                @click="addItem"
            />
        </template>
    </Dialog>
</template>