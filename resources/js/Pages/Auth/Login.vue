<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { InputText, Password, Button } from 'primevue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="$t('public.login')" />

        <form
            @submit.prevent="submit"
            class="flex flex-col gap-5 self-stretch"
        >
            <div class="flex flex-col gap-1 items-start self-stretch">
                <InputLabel
                    for="email"
                    :value="$t('public.email')"
                />

                <InputText
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    autofocus
                    autocomplete="username"
                    :placeholder="$t('public.enter_your_email')"
                    :invalid="!!form.errors.email"
                />

                <InputError :message="form.errors.email" />
            </div>

            <div class="flex flex-col gap-1 items-start self-stretch">
                <InputLabel
                    for="password"
                    :value="$t('public.password')"
                />

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

            <div class="flex flex-col gap-1 items-center self-stretch">
                <Button
                    type="submit"
                    class="w-full"
                    :disabled="form.processing"
                    :label="$t('public.login')"
                />
            </div>
        </form>
    </GuestLayout>
</template>
