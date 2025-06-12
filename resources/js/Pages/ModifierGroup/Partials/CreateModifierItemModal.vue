<script setup>
import { Dialog, Button, InputText, InputNumber } from 'primevue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    visible: Boolean,
});

const show = ref(false);

const form = useForm({
    name: '',
    price: null,
});

watch(() => props.visible, (val) => {
    show.value = val;
});

</script>

<template>
    <Dialog
        v-model:visible="show"
        modal
        class="dialog-sm"
    >
        <template #header>
            <div class="text-lg font-bold">{{ $t('public.create_modifier_item') }}</div>
        </template>
        <form 
            @submit.prevent="submitForm"
            class="p-5 flex flex-col gap-5 self-stretch"
        >
            <div class="flex flex-col items-start gap-3 self-stretch">
                <label for="name" class="font-bold">
                    {{ $t('public.modifier_name') }}
                </label>
                <InputText 
                    v-model="form.name"
                    class="w-full"
                    :placeholder="$t('public.modifier_name_placeholder')"
                />
            </div>
            <div class="flex flex-col items-start gap-3 self-stretch">
                <label for="name" class="font-bold">
                    {{ $t('public.price') }}
                </label>
                <InputNumber
                    v-model="form.price"
                    inputId="price"
                    class="w-full"
                    placeholder="0.00"
                    :min="0"
                    :maxFractionDigits="2"
                    prefix="RM "
                />
            </div>
        </form>
    </Dialog>
</template>