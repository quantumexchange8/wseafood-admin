<script setup>
import { Avatar, Button } from 'primevue';
import {generalFormat} from "@/Composables/format.js";
import {useLangObserver} from "@/Composables/localeObserver.js";
import { IconX } from '@tabler/icons-vue';

const props = defineProps({
    products: Object,
})

const emit = defineEmits(['remove']);

const { formatNameLabel } = generalFormat();
const { locale } = useLangObserver();

</script>

<template>
    <div 
        v-for="product in products"
        class="relative w-32 h-32 bg-slate-50 rounded-lg"
    >
        <div class="w-full h-full rounded-lg flex justify-center items-center self-stretch">
            <Avatar
            v-if="product.product_photo"
            :image="product.product_photo"
            class="w-20 h-20"
            />
            <Avatar
            v-else
            :label="formatNameLabel(JSON.parse(product.name)[locale] ?? JSON.parse(product.name)['en'])"
            class="w-20 h-20"
            />
        </div>
        <div
            class="absolute inset-0 right-2 top-2 flex justify-end z-10 rounded-lg"
        >
            <Button
                type="button"
                severity="secondary"
                rounded
                class="!p-0 !w-5 !h-5"
                @click="emit('remove', product)"
            >
                <template #icon>
                    <IconX :size="14" class="text-primary" />
                </template>
            </Button>
        </div>
    </div>
</template>