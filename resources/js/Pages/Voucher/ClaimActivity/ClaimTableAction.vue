<script setup>
import {Button, Dialog} from "primevue";
import {IconCheck, IconFileSearch} from "@tabler/icons-vue"
import {ref} from "vue";
import MarkAsUseForm from "@/Pages/Voucher/ClaimActivity/MarkAsUseForm.vue";
import ClaimDetail from "@/Pages/Voucher/ClaimActivity/ClaimDetail.vue";

const props = defineProps({
    redemption: Object
});

const visible = ref(false);
const dialog_type = ref('')

const openDialog = (type) => {
    visible.value = true;
    dialog_type.value = type;
};

const closeDialog = () => {
    visible.value = false;
}

const emit = defineEmits(['updated:redemption']);

const handleVoucherUpdate = (updatedAccount) => {
    emit('updated:redemption', updatedAccount);
};
</script>

<template>
    <div class="flex items-center justify-center gap-2">
        <Button
            v-if="redemption.status === 'redeemed'"
            type="button"
            severity="success"
            rounded
            size="small"
            icon="IconCheck"
            v-tooltip.top="$t('public.mark_as_used')"
            @click="openDialog('mark_as_used')"
        >
            <template #icon>
                <IconCheck stroke-width="1.5" />
            </template>
        </Button>
        <Button
            type="button"
            severity="secondary"
            rounded
            size="small"
            icon="IconFileSearch"
            v-tooltip.top="$t('public.view_detail')"
            @click="openDialog('view_detail')"
        >
            <IconFileSearch stroke-width="1.5" />
        </Button>
    </div>

    <Dialog
        v-model:visible="visible"
        modal
        :class="dialog_type === 'mark_as_used' ? 'dialog-sm' : 'dialog-sm md:dialog-lg'"
        :header="$t(`public.${dialog_type}`)"
    >
        <template v-if="dialog_type === 'mark_as_used'">
            <MarkAsUseForm
                :redemption="redemption"
                @update:visible="visible = $event"
                @updated:redemption="handleVoucherUpdate"
            />
        </template>

        <template v-if="dialog_type === 'view_detail'">
            <ClaimDetail
                :redemption="redemption"
            />
        </template>
    </Dialog>
</template>
