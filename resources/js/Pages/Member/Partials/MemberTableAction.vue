<script setup>
import { Button, TieredMenu } from 'primevue';
import { IconDotsVertical } from '@tabler/icons-vue';
import { ref } from 'vue';
import PointAdjustmentModal from './PointAdjustmentModal.vue';
import { trans } from 'laravel-vue-i18n';

const props = defineProps({
    member: Object,
});

const pointModalVisible = ref(false);
const adjustMember = ref(null);
const menu = ref();
const items = ref([
    {
        label: trans('public.point_adjustment'),
        command: () => {
            adjustPoint(props.member);
        },
    },
]);

const adjustPoint = (member) => {
    adjustMember.value = member;
    pointModalVisible.value = true;
}

const toggle = (event) => {
    menu.value.toggle(event);
};

</script>

<template>
    <Button
        type="button"
        rounded
        severity="secondary"
        variant="text"
        size="small"
        class="!p-2"
        @click="toggle"
    >
        <IconDotsVertical :size="14" :stroke-width="1.5" />
    </Button>
    <TieredMenu ref="menu" :model="items" popup>
        <template #item="{item}">
            <div class="p-2 text-sm cursor-pointer">
                {{ item.label }}
            </div>
        </template>
    </TieredMenu>

    <PointAdjustmentModal 
        :visible="pointModalVisible"
        :member="adjustMember"
        @update:visible="pointModalVisible = $event"
    />
</template>