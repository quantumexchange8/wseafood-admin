<script setup>
import {Card, DatePicker} from "primevue";
import {computed, ref} from "vue";
import MemberAnalysisChart from "@/Pages/Dashboard/MemberAnalysis/MemberAnalysisChart.vue";

const date = ref(new Date());

const selectedYear = computed(() => {
    return date.value ? date.value.getFullYear() : "";
});
</script>

<template>
    <Card class="w-full">
        <template #title>
            <div class="p-4 flex justify-between items-center self-stretch">
                <div class="font-medium">
                    {{ $t('public.member_analysis') }}
                </div>
                <DatePicker
                    v-model="date"
                    view="year"
                    dateFormat="yy"
                    inputClass="!w-20"
                    panelClass="!w-80"
                />
            </div>
        </template>
        <template #content>
            <div class="p-4">
                <MemberAnalysisChart
                    :selectedYear="selectedYear"
                />
            </div>
        </template>
    </Card>
</template>
