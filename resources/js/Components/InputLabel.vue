<script setup>
defineProps({
    value: {
        type: String,
    },
    invalid: Boolean
});
</script>

<template>
    <label
        class="block font-bold"
        :class="{
            'text-surface-700 dark:text-surface-300': !invalid,
            'text-red-500': invalid
        }"
    >
        <span v-if="value">
            {{ value }}<span class="text-red-500 text-sm font-medium"> *</span>
        </span>
        <span v-else>
            <slot />
        </span>
    </label>
</template>
