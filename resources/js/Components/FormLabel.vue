<script setup>
defineProps({
    label: String,
    invalid: Boolean,
    required: Boolean,
});
</script>

<template>
    <label
        class="font-medium text-sm"
        :class="{
            'text-surface-950 dark:text-white': !invalid,
            'text-red-500': invalid
        }"
    >
        <!-- If required -->
        <template v-if="required">
            <span v-if="label" class="flex gap-1">
                {{ label }}
                <span class="text-red-500">*</span>
            </span>
            <span v-else class="flex gap-1">
                <slot />
                <span class="text-red-500">*</span>
            </span>
        </template>

        <!-- If not required -->
        <template v-else>
            <span v-if="label">{{ label }}</span>
            <slot v-else />
        </template>
    </label>
</template>
