<script setup>
import { Dialog, Button, InputNumber } from 'primevue';
import { ref, watch } from 'vue';
import {useLangObserver} from "@/Composables/localeObserver.js";
import InputError from '@/Components/InputError.vue';
import { trans } from 'laravel-vue-i18n';

const props = defineProps({
    visible: Boolean,
    item: Object,
});

const emit = defineEmits(['update:visible', 'update:priceUpdated']);

const { locale } = useLangObserver();

const show = ref(false);
const price = ref(null);
const priceError = ref();

const submitForm = () => {
    if(price.value >= 0) {
        emit('update:priceUpdated', {
            id: props.item.id,
            price: price.value,
        });
        price.value = null;
        show.value = false;
        priceError.value = null;
    } else {
        priceError.value = trans('public.price_error_message');
    }
    
};

watch(() => props.visible, (val) => {
    show.value = val;
});

watch(show, (val) => {
    if (val !== props.visible) {
        emit('update:visible', val);
    }
});

watch(() => props.item, () => {
    if (props.item && typeof props.item.price !== 'undefined') {
        price.value = props.item.price;
    }
}, { immediate: true });

</script>

<template>
    <Dialog
        v-model:visible="show"
        modal
        class="dialog-sm"
    >
        <template #header>
            <div class="text-lg font-bold">
                {{ $t('public.edit_price') }} : {{ JSON.parse(item.modifier_name)[locale] ?? JSON.parse(item.modifier_name)['en'] }}
            </div>
        </template>
        <form 
            @submit.prevent="submitForm"
            class="p-5 flex flex-col gap-5 self-stretch"
        >
            <div class="flex flex-col items-start gap-3 self-stretch">
                <label for="price" class="font-bold">
                    {{ $t('public.price') }}
                </label>
                <div class="relative w-full">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                    RM
                    </span>
                    <InputNumber
                        v-model="price"
                        placeholder="0.00"
                        :min="0"
                        :maxFractionDigits="2"
                        inputClass="pl-12 w-full"
                        inputId="price"
                        fluid
                    />
                </div>
                <InputError :message="priceError" />
            </div>
        </form>
        <template #footer>
            <Button
                type="button"
                label="Cancel"
                severity="secondary"
                outlined
                @click="()=>{
                    price=null;
                    show = false;
                    priceError = null;
                }"
            />
            <Button
                type="button"
                label="Update"
                @click="submitForm"
            />
        </template>
    </Dialog>
</template>