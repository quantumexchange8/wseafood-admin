<script setup>
import { Dialog, Button, InputText } from 'primevue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    visible: Boolean,
    modifierItem: Object,
});

const emit = defineEmits(['update:visible', 'update:itemCreated']);

const show = ref(false);
const availableLocales = JSON.parse(usePage().props.availableLocales);
const modifierName = computed(() => {
    if (!props.modifierItem || !props.modifierItem.name) return {};
    try {
        return typeof props.modifierItem.name === 'string'
            ? JSON.parse(props.modifierItem.name)
            : props.modifierItem.name;
    } catch {
        return {};
    }
});

const form = useForm({
    id: props.modifierItem?.id ?? '',
    modifier_name: modifierName.value ?? {},
});

const submitForm = () => {
    const path = ref('modifier.item.store');
    if(props.modifierItem) {
        path.value = 'modifier.item.update';
    }

    form.post(route(path.value), {
        onSuccess: () => {
            form.reset();
            show.value = false;
            emit('update:itemCreated', true);
        },
    })
};

watch(() => props.visible, (val) => {
    show.value = val;
});

watch(show, (val) => {
    if (val !== props.visible) {
        emit('update:visible', val);
    }
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
            <div 
                v-for="lang in availableLocales"
                :key="lang"
                class="flex flex-col items-start gap-3 self-stretch"
            >
                <label :for="`name-${lang.value}`" class="font-bold">
                    {{ $t('public.modifier_name') }} ({{ lang.label }})
                </label>
                <InputText 
                    v-model="form.modifier_name[lang.value]"
                    :id="`name-${lang.value}`"
                    class="w-full"
                    :placeholder="$t('public.modifier_name_placeholder')"
                />
                <InputError :message="form.errors[`modifier_name.${lang.value}`]" />
            </div>
        </form>
        <template #footer>
            <Button
                type="button"
                :label="$t('public.cancel')"
                severity="secondary"
                outlined
                @click="show = false"
                :disabled="form.processing"
            />
            <Button
                type="submit"
                :label="modifierItem ? $t('public.save') : $t('public.create')"
                @click="submitForm"
                :disabled="form.processing"
            />
        </template>
    </Dialog>
</template>