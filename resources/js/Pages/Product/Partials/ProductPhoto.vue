<script setup>
import { Avatar, Image } from 'primevue';
import { IconEyeCancel } from '@tabler/icons-vue';
import { computed } from 'vue';

const props = defineProps({
    product: Object,
    layout: String,
})

const avatarClass = computed(() => {
    if (props.layout === 'grid') return 'w-40 h-40 object-contain';
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
    <div class="relative w-full bg-surface-100 dark:bg-surface-800 rounded-lg">
        <div
            :class="[
                containerClass,
                'flex justify-center items-center self-stretch'
            ]"
        >
            <Image
                v-if="product.product_photo"
                :src="product.product_photo"
                :image-class="[avatarClass, 'rounded-md']"
                class="bg-surface-200 dark:bg-surface-700 rounded-md"
            />
            <Avatar
                v-else
                :label="product.product_code"
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
