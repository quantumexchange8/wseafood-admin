<script setup>
import { Avatar } from 'primevue';
import {generalFormat} from "@/Composables/format.js";
import {useLangObserver} from "@/Composables/localeObserver.js";
import { IconEyeCancel } from '@tabler/icons-vue';
import { computed } from 'vue';

const props = defineProps({
    product: Object,
    layout: String,
})

const { formatNameLabel } = generalFormat();
const { locale } = useLangObserver();

const avatarClass = computed(() => {
    if (props.layout === 'grid') return 'w-40 h-40';
    if (props.layout === 'modal') return 'w-10 h-10';
    return 'w-24 h-24'; // default to 'list'
});

const containerClass = computed(() => {
    if (props.layout === 'grid') return 'px-3 py-10 w-full h-full rounded-lg';
    if (props.layout === 'modal') return 'px-1 py-3 w-full h-full rounded-lg';
    return 'px-2 py-2 w-full h-full rounded-lg'; // default to 'list'
});

const maskGapClass = computed(() => {
    if (props.layout === 'grid') return 'gap-3';
    return 'gap-0.5'; // default to 'list' & 'modal'
});

const eyeClass = computed(() => {
    if (props.layout === 'grid') return 'w-10 h-10';
    if (props.layout === 'modal') return 'w-2 h-2';
    return 'w-8 h-8'; // default to 'list'
});

const textClass = computed(() => {
    if (props.layout === 'grid') return 'text-sm';
    return 'text-xxs'; // default to 'list' & modal
});
</script>

<template>
    <div class="relative w-full bg-slate-50">
        <div
            :class="[
                containerClass,
                'flex justify-center items-center self-stretch'
            ]"
        >
            <Avatar
            v-if="product.product_photo"
            :image="product.product_photo"
            :class="avatarClass"
            />
            <Avatar
            v-else
            :label="formatNameLabel(JSON.parse(product.name)[locale] ?? JSON.parse(product.name)['en'])"
            :class="avatarClass"
            size="large"
            />
        </div>
        <div
            v-if="product.status === 'inactive'"
            class="absolute inset-0 bg-gradient-to-br from-white to-primary-600/10 flex items-center justify-center z-10 rounded-lg">
            <div 
                :class="[
                    maskGapClass,
                    'px-2.5 flex flex-col justify-center items-center'
                ]"
            >
                <IconEyeCancel
                    :class="[
                        eyeClass,
                        'text-primary-500'
                    ]"
                />
                <span 
                    :class="[
                        textClass,
                        'font-bold text-primary-400 text-center'
                    ]"
                >
                    {{ $t('public.item_hidden') }}
                </span>
            </div>
        </div>
    </div>
</template>