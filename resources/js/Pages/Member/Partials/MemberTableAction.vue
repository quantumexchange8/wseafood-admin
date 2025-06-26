<script setup>
import { Button, Dialog, TieredMenu } from 'primevue';
import { IconDotsVertical } from '@tabler/icons-vue';
import { ref } from 'vue';
import { trans } from 'laravel-vue-i18n';
import PointAdjustmentForm from "@/Pages/Member/Partials/PointAdjustmentForm.vue";

const props = defineProps({
    member: Object,
});


const visible = ref(false);
const dialogType = ref('');

const menu = ref();
const items = ref([
    {
        label: trans('public.point_adjustment'),
        command: () => {
            visible.value = true;
            dialogType.value = 'point_adjustment'
        },
    },
]);

const toggle = (event) => {
    menu.value.toggle(event);
};

// Ref to the child form component
const formRef = ref(null)

const handleSaveChanges = () => {
    formRef.value?.submitForm()
}
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

    <Dialog
        v-model:visible="visible"
        modal
        class="dialog-xs md:dialog-sm"
    >
        <template #header>
            <div class="text-lg font-bold">
                {{ $t('public.point_adjustment') }}
            </div>
        </template>

        <template v-if="dialogType === 'point_adjustment'">
            <PointAdjustmentForm
                ref="formRef"
                :member="member"
            />
        </template>

        <template #footer>
            <Button
                :label="$t('public.cancel')"
                severity="secondary"
                outlined
                @click="visible = false"
            />
            <Button
                :label="$t('public.save_changes')"
                @click="handleSaveChanges"
            />
        </template>
    </Dialog>
</template>
