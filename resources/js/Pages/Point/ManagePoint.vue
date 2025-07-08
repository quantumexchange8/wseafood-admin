<script setup>
import {Button, Dialog, InputNumber, InputText, Select} from "primevue";
import {IconPlus} from "@tabler/icons-vue";
import {ref, watch} from "vue";
import {useForm} from "@inertiajs/vue3";
import {generalFormat} from "@/Composables/format.js";
import InputLabel from "@/Components/InputLabel.vue";
import debounce from "lodash/debounce.js";
import SelectChipGroup from "@/Components/SelectChipGroup.vue";
import InputError from "@/Components/InputError.vue";

const visible = ref(false);
const {formatAmount} = generalFormat();

const openDialog = () => {
    visible.value = true;
}

const form = useForm({
    user_id: '',
    method: '',
    point: '',
    remark: '',
});

const members = ref([])
const searchKeyword = ref('')
const selectedMember = ref(null)
const loadingMembers = ref(false)

let controller

const getMembers = async () => {
    if (controller) controller.abort()
    controller = new AbortController()
    loadingMembers.value = true
    try {
        const response = await axios.get(route('getMembers'), {
            params: { keyword: searchKeyword.value },
            signal: controller.signal
        })
        members.value = response.data
    } catch (error) {
        if (error.code === 'ERR_CANCELED') {
            console.log('Request canceled')
        } else {
            console.error(error)
        }
    } finally {
        loadingMembers.value = false
    }
}

watch(
    searchKeyword,
    debounce(() => {
        if (searchKeyword.value) {
            getMembers()
        } else {
            members.value = []
        }
    }, 100)
);

const methods = [
    {label: 'receive', value: 'point_in'},
    {label: 'spend', value: 'point_out'}
];

const submitForm = () => {
    form.user_id = selectedMember.value?.id;
    form.post(route('point.manage_point'), {
        onSuccess: () => {
            closeDialog();
            form.reset();
            searchKeyword.value = '';
            selectedMember.value = null;
        }
    })
}

const closeDialog = () => {
    visible.value = false;
}
</script>

<template>
    <Button
        type="button"
        :label="$t('public.manage_point')"
        @click="openDialog"
    >
        <template #icon>
            <IconPlus :size="20"/>
        </template>
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        class="dialog-xs md:dialog-sm"
    >
        <template #header>
            <div class="text-lg font-bold">
                {{ $t('public.manage_point') }}
            </div>
        </template>

        <form class="flex flex-col gap-5 self-stretch">
            <div class="px-5 pt-5 flex items-center gap-5 justify-between self-stretch bg-gradient-to-b from-primary-50 to-white dark:from-primary-950/40 dark;to-surface-900">
                <div class="flex flex-col items-start">
                    <div class="text-lg font-bold">
                        {{ selectedMember?.full_name ?? $t('public.member') }}
                    </div>
                    <div class="text-sm text-surface-500">
                        {{ selectedMember?.phone_number ?? $t('public.phone_number') }}
                    </div>
                </div>
                <div class="text-lg font-bold">
                    {{ formatAmount(form.point, 0, '') }} PTS
                </div>
            </div>

            <div class="flex flex-col gap-5 items-center self-stretch px-5 pb-5">
                <div class="flex flex-col items-start gap-3 self-stretch">
                    <InputLabel
                        for="adjust-point"
                        :value="$t('public.select_member')"
                    />
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <Select
                            v-model="selectedMember"
                            editable
                            input-id="adjust-point"
                            :options="members"
                            optionLabel="full_name"
                            :placeholder="$t('public.search_member')"
                            class="w-full"
                            :loading="loadingMembers"
                            @input="searchKeyword = $event.target.value"
                            :invalid="!!form.errors.user_id"
                        >
                            <template #value="{value, placeholder}">
                                <div v-if="value">
                                    {{ value.full_name }}
                                </div>
                                <span v-else class="text-surface-400">{{ placeholder }}</span>
                            </template>
                            <template #option="{option}">
                                <div class="flex flex-col">
                                    <span>{{ option.full_name }}</span>
                                    <span class="text-xs text-surface-500">{{ option.phone_number }}</span>
                                </div>
                            </template>
                        </Select>
                        <InputError :message="form.errors.user_id" />
                    </div>
                </div>

                <div class="flex flex-col items-start gap-3 self-stretch">
                    <InputLabel
                        for="method"
                        :value="$t('public.method')"
                    />
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <SelectChipGroup
                            v-model="form.method"
                            :items="methods"
                            value-key="value"
                        >
                            <template #option="{ item }">
                                <span class="uppercase">{{ $t(`public.${item.label}`) }}</span>
                            </template>
                        </SelectChipGroup>
                        <InputError :message="form.errors.method" />
                    </div>
                </div>

                <div class="flex flex-col items-start gap-3 self-stretch">
                    <InputLabel
                        for="point"
                        :value="$t('public.point')"
                    />
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <div class="relative w-full">
                            <InputNumber
                                v-model="form.point"
                                placeholder="e.g. 1"
                                inputClass="pr-12 w-full"
                                inputId="point"
                                fluid
                                :invalid="!!form.errors.point"
                            />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 z-10 text-sm">PTS</span>
                        </div>
                        <InputError :message="form.errors.point" />
                    </div>
                </div>

                <div class="flex flex-col items-start gap-3 self-stretch">
                    <div class="flex items-center gap-2">
                        <InputLabel
                            for="remark"
                            :value="$t('public.remark')"
                        />
                    </div>
                    <InputText
                        v-model="form.remark"
                        :placeholder="$t('public.point_adjustment_placeholder')"
                        inputId="remark"
                        class="w-full"
                        :invalid="!!form.errors.remark"
                    />
                    <InputError :message="form.errors.remark" />
                </div>
            </div>
        </form>

        <template #footer>
            <Button
                type="button"
                :label="$t('public.cancel')"
                severity="secondary"
                outlined
                @click.prevent="closeDialog"
                :disabled="form.processing"
            />
            <Button
                type="submit"
                :label="$t('public.save_changes')"
                @click.prevent="submitForm"
                :disabled="form.processing"
            />
        </template>
    </Dialog>
</template>
