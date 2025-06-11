<script setup>
import { Avatar } from 'primevue';
import {generalFormat} from "@/Composables/format.js";
import {useLangObserver} from "@/Composables/localeObserver.js";
import { IconEyeCancel } from '@tabler/icons-vue';

const props = defineProps({
    product: Object,
    layout: String,
})

const { formatNameLabel } = generalFormat();
const { locale } = useLangObserver();

</script>

<template>
    <div class="relative w-full bg-slate-50">
        <div
            :class="[
                layout === 'grid'
                    ? 'px-3 py-10 w-full h-full rounded-lg'
                    : 'px-2 py-2 w-full h-full rounded-lg',
                    'flex justify-center items-center self-stretch'
            ]"
        >
            <Avatar
            v-if="product.product_photo"
            :image="product.product_photo"
            :class="layout === 'grid' ? 'w-40 h-40' : 'w-24 h-24'"
            />
            <Avatar
            v-else
            :label="formatNameLabel(product.name[locale] ?? product.name['en'])"
            :class="layout === 'grid' ? 'w-40 h-40' : 'w-24 h-24'"
            size="large"
            />
        </div>
        <div
            v-if="product.status === 'inactive'"
            class="absolute inset-0 bg-gradient-to-br from-white to-primary-600/10 flex items-center justify-center z-10 rounded-lg">
            <div 
                :class="[
                    layout === 'grid' ? 'gap-3' : 'gap-0.5',
                    'px-2.5 flex flex-col justify-center items-center'
                ]"
            >
                <IconEyeCancel
                    :class="[
                        layout === 'grid' ? 'w-10 h-10' : 'w-8 h-8',
                        'text-primary-500'
                    ]"
                />
                <span 
                    :class="[
                        layout === 'grid' ? 'text-sm' : 'text-xxs',
                        'font-bold text-primary-400 text-center'
                    ]"
                >
                    {{ $t('public.item_hidden') }}
                </span>
            </div>
        </div>
    </div>
</template>