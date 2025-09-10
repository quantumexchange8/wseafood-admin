<script setup>
import { computed } from 'vue'

const props = defineProps({
    options: { type: Array, required: true }, // e.g. ['active','ended']
    modelValue: { type: [Array, String, Number], default: () => [] },
    multiple: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'select'])

const isSelected = (value) => {
    if (props.multiple) {
        return Array.isArray(props.modelValue) && props.modelValue.includes(value)
    }
    return props.modelValue === value
}

const toggleSelect = (value) => {
    let newValue

    if (props.multiple) {
        newValue = Array.isArray(props.modelValue) ? [...props.modelValue] : []
        const index = newValue.indexOf(value)

        if (index === -1) {
            newValue.push(value)
        } else {
            newValue.splice(index, 1)
        }
    } else {
        newValue = props.modelValue === value ? null : value
    }

    emit('update:modelValue', newValue)
    emit('select', newValue)
}
</script>

<template>
    <div class="flex gap-2 flex-wrap">
        <div
            v-for="option in options"
            :key="option.value ?? option"
            class="flex items-center justify-center px-3 py-2 rounded-full min-w-11 border shadow-input cursor-pointer select-none transition-colors duration-200"
            :class="{
                'bg-primary-100 dark:bg-primary-700/40 border-primary': isSelected(option.value ?? option),
                'bg-white dark:bg-surface-900 border-surface-300 dark:border-surface-700': !isSelected(option.value ?? option)
            }"
            @click="toggleSelect(option.value ?? option)"
        >
            <!-- Slot for custom option content -->
            <slot name="option" :option="option" :selected="isSelected(option.value ?? option)">
                <!-- Default fallback if no slot provided -->
                <div class="text-xs font-bold">
                    {{ option.label ?? option }}
                </div>
            </slot>
        </div>
    </div>
</template>
