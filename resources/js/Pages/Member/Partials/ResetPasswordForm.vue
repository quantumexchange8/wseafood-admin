<script setup>
import { Password } from 'primevue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from "@/Components/InputLabel.vue";

const props = defineProps({
    member: Object,
});

const form = useForm({
    password: null,
    password_confirmation: null,
    memberId: props.member.id,
});

const emit = defineEmits(['update:visible']);

const submitForm = () => {
    form.post(route('member.resetPassword'), {
        onSuccess: () => {
            form.reset();
            emit('update:visible', false);
        },
    })
};

defineExpose({
    submitForm
});
</script>

<template>
    <form
        @submit.prevent="submitForm"
        class="flex flex-col gap-5 self-stretch"
    >
        <div class="px-5 pt-5 flex items-center gap-5 justify-between self-stretch bg-gradient-to-b from-primary-50 to-white dark:from-primary-950/40 dark;to-surface-900">
            <div class="flex flex-col items-start">
                <div class="text-lg font-bold">
                    {{ member.full_name }}
                </div>
                <div class="text-sm text-surface-500">
                    {{ member.id_number }}
                </div>
            </div>
        </div>
        <div class="flex flex-col gap-5 items-center self-stretch px-5 pb-5">
            <div class="flex flex-col items-start gap-3 self-stretch">
                <div class="flex items-center gap-2">
                    <InputLabel
                        for="password"
                        :value="$t('public.new_password')"
                    />
                </div>
                <Password
                    id="password"
                    class="block w-full"
                    v-model="form.password"
                    toggleMask
                    :inputStyle="{'width': '100%'}"
                    :style="{'width': '100%'}"
                    :invalid="!!form.errors.password"
                    :feedback="false"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.password" />
            </div>
            <div class="flex flex-col items-start gap-3 self-stretch">
                <div class="flex items-center gap-2">
                    <InputLabel
                        for="password-confirm"
                        :value="$t('public.confirm_password')"
                    />
                </div>
                <Password
                    id="password-confirm"
                    class="block w-full"
                    v-model="form.password_confirmation"
                    toggleMask
                    :inputStyle="{'width': '100%'}"
                    :style="{'width': '100%'}"
                    :invalid="!!form.errors.password_confirmation"
                    :feedback="false"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>
        </div>
    </form>
</template>
