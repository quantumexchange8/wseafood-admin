<script setup>
import { IconX } from '@tabler/icons-vue';
import { Button } from 'primevue';
import LeavingConfirmation from '@/Pages/Product/Partials/LeavingConfirmation.vue';
import { ref } from 'vue';

const leaveConfirm = ref(null);
const disableBtn = ref(false);

const leaveConfirmVisible = () => {
    if(leaveConfirm.value) {
        leaveConfirm.value.leavingPage();
    }
};

</script>

<template>
    <div class="p-1 flex flex-col justify-center items-center gap-4 self-stretch">
        <!-- Slot for custom image or content -->
        <slot name="image">
            <!-- Default image content if no custom content is provided -->
            <IconX class="w-16 h-16 text-surface-700 dark:text-surface-300" />
        </slot>

        <div class="flex flex-col items-center gap-2 self-stretch">
            <!-- Slot for custom title -->
            <slot name="title">
                <div class="self-stretch text-gray-950 dark:text-white text-center font-semibold text-sm md:text-base">
                    {{ $t('public.oops') }}
                </div>
            </slot>

            <!-- Slot for custom message -->
            <slot name="message">
                <div class="self-stretch text-gray-700 dark:text-gray-300 text-center text-xs md:text-sm">
                    {{ $t('public.empty_modifier_group_message') }}
                </div>
            </slot>
        </div>

        <Button 
            type="button"
            :label="$t('public.go_to_create')"
            @click="leaveConfirmVisible"
            :disabled="disableBtn"
        />
    </div>

    <LeavingConfirmation ref="leaveConfirm" @update:leave="disableBtn = true"/>
</template>
