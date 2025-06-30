<script setup>
import {Image} from "primevue";

defineProps({
    width: {
        type: String,
        default: "40",
    },
})
</script>

<template>
    <Image src="/img/logo/logo_mark.png" alt="logo" :width="width" />
</template>
