<script setup>
import { IconAlertCircle } from '@tabler/icons-vue';

const props = defineProps({
    message: String,
});
</script>

<template>
    <div
        v-tooltip.top="message"
        class="hover:text-primary-500"
    >
        <IconAlertCircle :size="16" />
    </div>
</template>
