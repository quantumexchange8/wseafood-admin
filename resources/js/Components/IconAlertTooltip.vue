<script setup>
import { IconAlertCircle } from '@tabler/icons-vue';

const props = defineProps({
    message: String,
});
</script>

<template>
    <div
        v-tooltip.top="message"
        class="text-surface-500 dark:text-surface-400 hover:text-primary-500 dark:hover:text-primary"
    >
        <IconAlertCircle :size="16" />
    </div>
</template>
