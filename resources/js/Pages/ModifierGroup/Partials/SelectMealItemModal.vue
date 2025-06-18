<script setup>
import { Dialog, Button, Tree, Avatar, Tag, useToast } from 'primevue';
import { ref, watch, onMounted, watchEffect, computed } from 'vue';
import {useLangObserver} from "@/Composables/localeObserver.js";
import {generalFormat} from "@/Composables/format.js";
import { usePage } from '@inertiajs/vue3';
import LoadingMask from '@/Pages/ModifierGroup/Partials/LoadingMask.vue';
import { trans } from 'laravel-vue-i18n';

const props = defineProps({
    visible: Boolean,
    categoryCount: Number,
    updateSelected: Object,
});

const emit = defineEmits(['update:visible', 'update:addMeal', 'update:selectedKeys']);

const { locale } = useLangObserver();
const { formatNameLabel,formatAmount } = generalFormat();

const toast = useToast();
const show = ref(false);
const isLoading = ref(false);
const fetchedItem = ref([]);
const selectedkey = ref();

const loadLazyData = async () => {
    try{
        isLoading.value = true
        const response = await axios.get(route('modifier.categoryProduct.fetch'));
        fetchedItem.value = response.data.data;
        
    } catch (e) {
        console.log(e);
        
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    loadLazyData();
})

watchEffect(() => {
    if (usePage().props.toast !== null) {
        loadLazyData();
    }
});

watch(() => props.visible, (val) => {
    show.value = val;

    if(val) {
        if(props.updateSelected) {
            selectedkey.value = props.updateSelected;
        }
    }
});

watch(show, (val) => {
    if (val !== props.visible) {
        emit('update:visible', val);
    }
});

const getSelectedLeafNodes = (nodes, keys) => {
    let selected = [];
    if (Array.isArray(nodes)) {
        nodes.forEach(node => {
            if (node.children && node.children.length) {
                selected = selected.concat(getSelectedLeafNodes(node.children, keys));
            } else if (keys && keys[node.key] && keys[node.key].checked) {
                selected.push(node);
            }
        });
    }
    return selected;
};

const addItem = () => {
    const selectedLeafNodes = getSelectedLeafNodes(fetchedItem.value, selectedkey.value);

    emit('update:addMeal', [selectedLeafNodes, selectedkey.value]);
    toast.add({ 
        severity: 'success', 
        summary: trans('public.meal_item_added_success'), 
        detail: trans('public.meal_item_added_success_caption'), 
        life: 3000 
    })
    show.value = false;
}

const totalSelectedItem = computed(() => {
    let count = 0;
    const nodes = fetchedItem.value;
    const keys = selectedkey.value;

    const countSelectedChildren = (nodes) => {
        let total = 0;
        if (Array.isArray(nodes)) {
            nodes.forEach(node => {
                if (node.children && node.children.length) {
                    total += countSelectedChildren(node.children);
                } else if (keys && keys[node.key] && keys[node.key].checked) {
                    total += 1;
                }
            });
        }
        return total;
    };
    count = countSelectedChildren(nodes);
    return count;
})

const cancelAction = () => {
    selectedkey.value = null;
    show.value = false;
};

function updateParentSelectionState(nodes, selectedKeys) {
    // Recursively update parent nodes
    const updateNode = (node) => {
        if (node.children && node.children.length) {
            let checkedCount = 0;
            let partialCount = 0;
            node.children.forEach(child => {
                updateNode(child);
                const key = child.key;
                if (selectedKeys[key]?.checked) checkedCount++;
                if (selectedKeys[key]?.partialChecked) partialCount++;
            });

            if (checkedCount === node.children.length) {
                selectedKeys[node.key] = { checked: true, partialChecked: false };
            } else if (checkedCount > 0 || partialCount > 0) {
                selectedKeys[node.key] = { checked: false, partialChecked: true };
            } else {
                delete selectedKeys[node.key];
            }
        }
    };

    if (Array.isArray(nodes)) {
        nodes.forEach(updateNode);
    }
}

function removeNodeAndChildren(key) {
    // Helper to recursively remove a node and its children from selectedkey
    const removeRecursively = (nodes, keyToRemove) => {
        if (!Array.isArray(nodes)) return;
        nodes.forEach(node => {
            if (node.key === keyToRemove) {
                // Remove this node and all its children
                delete selectedkey.value[node.key];
                if (node.children && node.children.length) {
                    node.children.forEach(child => removeRecursively([child], child.key));
                }
            } else if (node.children && node.children.length) {
                removeRecursively(node.children, keyToRemove);
            }
        });
    };
    removeRecursively(fetchedItem.value, key);
    updateParentSelectionState(fetchedItem.value, selectedkey.value);
    
    emit('update:selectedKeys', { ...selectedkey.value });
}

defineExpose({
    removeNodeAndChildren
})

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
                <LoadingMask :itemCount="categoryCount" />
            </template>

            <template #default="slotProps">
            <div class="flex items-center gap-2">
                <div class="font-bold">
                    {{ JSON.parse(slotProps.node.name)[locale] ?? JSON.parse(slotProps.node.name)['en'] }}
                </div>
                <Tag 
                    v-if="slotProps.node.children && slotProps.node.children.length"
                    :value="`${slotProps.node.children.filter(child => selectedkey && selectedkey[child.key] && selectedkey[child.key].checked).length} ${$t('public.selected')}`"
                    rounded
                    v-show="slotProps.node.children.filter(child => selectedkey && selectedkey[child.key] && selectedkey[child.key].checked).length > 0"
                />
            </div>
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
                    {{ JSON.parse(slotProps.node.name)[locale] ?? JSON.parse(slotProps.node.name)['en'] }}
                    </div>
                </div>
                <div>
                    {{ formatAmount(slotProps.node.price, 2, 'RM') }}
                </div>
                </div>
            </div>
            </template>
        </Tree>

        <template #footer>
            <div class="w-full flex justify-between items-center">
                <div class="text-sm text-slate-400">
                    {{ $t('public.selected') }}: {{ totalSelectedItem }}
                </div>
                <div class="flex gap-3">
                    <Button
                        type="button"
                        :label="$t('public.cancel')"
                        severity="secondary"
                        outlined
                        @click="cancelAction"
                    />
                    <Button
                        type="submit"
                        :label="$t('public.add')"
                        @click="addItem"
                    />
                </div>
            </div>
        </template>
    </Dialog>
</template>