<script setup>
import { IconX } from '@tabler/icons-vue';
import { defineProps } from 'vue';

const props = defineProps({
    title: String,
    message: String
});
</script>

<template>
    <div class="flex flex-col justify-center items-center gap-5 self-stretch">
        <!-- Slot for custom image or content -->
        <slot name="image">
            <!-- Default image content if no custom content is provided -->
            <IconX class="w-16 h-16 text-surface-700 dark:text-surface-300" />
        </slot>

        <div class="flex flex-col items-center gap-2 self-stretch">
            <!-- Slot for custom title -->
            <slot name="title">
                <div v-if="title" class="self-stretch text-gray-950 dark:text-white text-center font-semibold text-sm md:text-base">
                    {{ title }}
                </div>
            </slot>

            <!-- Slot for custom message -->
            <slot name="message">
                <div v-if="message" class="self-stretch text-gray-700 dark:text-gray-300 text-center text-xs md:text-sm">
                    {{ message }}
                </div>
            </slot>
        </div>
    </div>
</template>
