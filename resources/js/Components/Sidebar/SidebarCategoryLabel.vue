<script setup>
import { sidebarState } from '@/Composables'

defineProps({
    title: String,
})
</script>

<template>
    <div
        class="pt-4 text-xs text-left w-full text-gray-600 dark:text-gray-400 uppercase"
        :class="{
            'translate-x-0 px-3':
                sidebarState.isOpen || sidebarState.isHovered,
            '-translate-x-full lg:w-[84px] lg:translate-x-0 px-5 hidden':
                !sidebarState.isOpen && !sidebarState.isHovered,
        }"
    >
        {{ title }}
    </div>
</template>
