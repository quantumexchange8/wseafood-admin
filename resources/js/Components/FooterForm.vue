<script setup>
import {sidebarState} from "@/Composables/index.js";
</script>

<template>
    <div class="pb-12"></div>
    <div
        class="fixed bottom-5 left-6 right-6 bg-white dark:bg-surface-900 z-50 border-t border-primary-200 dark:border-primary-900/40 shadow-md"
        :class="[
            {
              'lg:ml-[252px]': sidebarState.isOpen,
              'md:ml-0 lg:ml-[84px]': !sidebarState.isOpen,
            },
        ]"
    >
        <div class="px-5 py-4 max-w-[1440px] mx-auto">
            <slot />
        </div>
    </div>
</template>
