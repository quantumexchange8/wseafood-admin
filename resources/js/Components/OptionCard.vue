<template>
    <div
        class="flex items-center justify-center p-3 rounded-xl min-w-11 border border-surface-300 dark:border-surface-700 shadow-input cursor-pointer select-none text-surface-950 dark:text-white hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors duration-200"
        :class="{
      'bg-surface-300 dark:bg-surface-700': active,
      'bg-white dark:bg-surface-900': !active
    }"
        @click="$emit('select', value)"
    >
        <slot />
    </div>
</template>

<script setup>
defineProps({
    value: {
        type: String,
        required: true
    },
    active: {
        type: Boolean,
        default: false
    }
})

defineEmits(["select"])
</script>
