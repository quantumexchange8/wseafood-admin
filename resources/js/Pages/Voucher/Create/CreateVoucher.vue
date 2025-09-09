<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import CreateVoucherForm from "@/Pages/Voucher/Create/CreateVoucherForm.vue";
</script>

<template>
    <AuthenticatedLayout :title="$t('public.create_voucher')">
        <div class="flex flex-col items-start gap-5 flex-1">
            <div class="flex flex-col items-start self-stretch">
                <h1 class="text-2xl font-bold">
                    {{ $t('public.create_voucher') }}
                </h1>
                <div class="self-stretch text-sm text-surface-500">
                    {{ $t('public.create_voucher_caption') }}
                </div>
            </div>

            <CreateVoucherForm />
        </div>
    </AuthenticatedLayout>
</template>
