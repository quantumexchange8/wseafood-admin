<script setup>
import { Card, Button, InputText, RadioButton, InputNumber, Checkbox, IconField, InputIcon } from 'primevue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {generalFormat} from "@/Composables/format.js";
import InputError from '@/Components/InputError.vue';
import {useLangObserver} from "@/Composables/localeObserver.js";
import IconAlertTooltip from '@/Pages/ModifierGroup/Partials/IconAlertTooltip.vue';
import { IconPlus } from '@tabler/icons-vue';
import SelectModifierItemModal from '@/Pages/ModifierGroup/Partials/SelectModifierItemModal.vue';

const props = defineProps({
    category: Object,
});

const { formatNameLabel } = generalFormat();
const { locale } = useLangObserver();

const availableLocales = JSON.parse(usePage().props.availableLocales);

const noMax = ref(false);
const selectModalVisible = ref(false);

const form = useForm({
    group_name: '',
    display_name: {},
    group_type: 'optional',
    min: 0,
    max: 1,
    override: '0',
    modifier_item: {},
    product: {},
});

const submitForm = () => {
console.log(form);

    // form.post(route('category.store'), {
    //     onSuccess: () => {
    //         form.reset();
    //         emit('formSubmitted', false);
    //     },
    //     onError: () => {
    //         emit('formSubmitted', false);
    //     },
    // })
};

watch((noMax), () => {
    if(noMax.value) {
        form.max = null;
    } else {
        form.max = 1;
    }
});

watch(() => form.group_type, () => {
    if(form.group_type === 'optional') {
        form.min = 0;
    } else {
        form.min = 1;
    }
});

</script>

<template>
    <form @submit.prevent="submitForm" class="flex flex-col items-start gap-4 self-stretch">
        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.group_detail') }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $t('public.group_detail_caption') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="py-5 flex flex-col items-start gap-5 self-stretch">
                    <div class="px-5 flex items-center gap-5 self-stretch">
                        <div class="w-1/5 flex items-center gap-1">
                            <label for="group-name" class="text-sm">
                                {{ $t('public.group_name') }}
                            </label>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <InputText
                            v-model="form.group_name"
                            id="group-name"
                            class="w-1/3"
                            :placeholder="$t('public.group_name_placeholder')"
                        />
                        <InputError :message="form.errors.group_name"/>
                    </div>

                    <div 
                        v-for="lang in availableLocales"
                        :key="lang"
                        class="px-5 flex items-center gap-5 self-stretch"
                    >
                        <div class="w-1/5 flex items-center gap-1">
                            <label :for="`display-name-${lang.value}`" class="text-sm">
                                {{ $t('public.display_name') }} ({{ lang.label }})
                            </label>
                            <IconAlertTooltip :message="$t('public.display_name_tooltip')" />
                        </div>
                        <InputText
                            v-model="form.display_name[lang.value]"
                            :id="`display-name-${lang.value}`"
                            class="w-1/3"
                            :placeholder="$t('public.display_name_placeholder')"
                        />
                        <InputError :message="form.errors[`display_name.${lang.value}`]" />
                    </div>

                    <div class="px-5 flex items-center gap-5 self-stretch">
                        <div class="w-1/5 flex items-center gap-1">
                            <div class="text-sm">
                                {{ $t('public.group_type') }}
                            </div>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <div class="flex items-center gap-5">
                            <div class="flex items-center gap-3">
                                <RadioButton v-model="form.group_type" inputId="required" value="required" />
                                <div class="flex items-center gap-2">
                                    <label for="required" class="text-sm">
                                        {{ $t('public.required') }}
                                    </label>
                                    <IconAlertTooltip :message="$t('public.required_tooltip')" />
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <RadioButton v-model="form.group_type" inputId="optional" value="optional" />
                                <div class="flex items-center gap-2">
                                    <label for="optional" class="text-sm">
                                        {{ $t('public.optional') }}
                                    </label>
                                    <IconAlertTooltip :message="$t('public.optional_tooltip')" />
                                </div>
                            </div>
                        </div>
                        <InputError :message="form.errors.group_type" />
                    </div>

                    <div class="px-5 flex items-center gap-5 self-stretch">
                        <div class="w-1/5 flex items-center gap-1">
                            <label for="max" class="text-sm">
                                {{ $t('public.min_max_selection') }}
                            </label>
                            <div class="text-xs text-red-500">
                                *
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="relative w-40">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                                Min.
                                </span>
                                <InputNumber
                                    v-model="form.min"
                                    placeholder="0"
                                    :min="0"
                                    inputClass="pl-12 w-full"
                                    readonly
                                />
                            </div>
                            
                            <div class="flex items-center">
                                <div class="w-16 h-px bg-slate-400"></div>
                            </div>

                            <div class="relative w-40">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 z-10 text-sm">
                                Max.
                                </span>
                                <InputText
                                    v-if="noMax"
                                    modelValue="∞"
                                    class="pl-12 w-full text-[18px]"
                                    readonly
                                />
                                <InputNumber
                                    v-else
                                    v-model="form.max"
                                    placeholder="0"
                                    :min="0"
                                    inputClass="pl-12 w-full"
                                />
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <Checkbox 
                                v-model="noMax" 
                                inputId="no_max"
                                binary 
                            />
                            <label for="no_max">
                                {{ $t('public.no_max_selection') }}
                            </label>
                        </div>
                        <InputError :message="form.errors.max"/>
                    </div>

                    <div class="px-5 flex items-center gap-5 self-stretch">
                        <div class="w-1/5 flex items-center gap-1">
                            <div class="text-sm">
                                {{ $t('public.allow_override') }}
                            </div>
                            <IconAlertTooltip :message="$t('public.allow_override_tooltip')" />
                        </div>
                        <div class="flex items-center gap-5">
                            <div class="flex items-center gap-3">
                                <RadioButton v-model="form.override" inputId="not_allowed" value="0" />
                                <div class="flex items-center gap-2">
                                    <label for="not_allowed" class="text-sm">
                                        {{ $t('public.not_allowed') }}
                                    </label>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <RadioButton v-model="form.override" inputId="allowed" value="1" />
                                <div class="flex items-center gap-2">
                                    <label for="allowed" class="text-sm">
                                        {{ $t('public.allowed') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <InputError :message="form.errors.override" />
                    </div>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.add_modifier_item') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="p-5 flex items-center self-stretch">
                    <Button
                        type="button"
                        :label="$t('public.add')"
                        @click="selectModalVisible = true"
                    >
                        <template #icon>
                            <IconPlus :size="20" />
                        </template>
                    </Button>
                </div>
            </template>
        </Card>

        <Card class="w-full">
            <template #title>
                <div class="px-5 py-3 flex justify-between items-center self-stretch">
                    <div class="text-lg font-bold">
                        {{ $t('public.add_to_meal_item') }}
                    </div>
                </div>
            </template>
            <template #content>
                <div class="p-5 flex items-center self-stretch">
                    <Button
                        type="button"
                        :label="$t('public.add')"
                    >
                        <template #icon>
                            <IconPlus :size="20" />
                        </template>
                    </Button>
                </div>
            </template>
        </Card>

        <div class="w-full px-5 py-4 flex justify-between items-center border-t border-solid border-primary-200 bg-white shadow-sm">
            <Button
                type="button"
                severity="secondary"
                outlined
                :label="$t('public.cancel')"
                @click="() => $inertia.visit(route('modifier_group.index'))"
            />
            <Button
                type="submit"
                severity="primary"
                :label="$t('public.submit')"
                :disabled="form.processing"
            />
        </div>
    </form>

    <SelectModifierItemModal :visible="selectModalVisible" />
</template>