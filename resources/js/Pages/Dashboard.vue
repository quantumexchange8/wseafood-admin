<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { IconDownload } from '@tabler/icons-vue';
import { Button, Dialog, Chip, Card, DataTable, Column, IconField, InputIcon, InputText, Tag, ProgressSpinner, Popover, Select, ToggleSwitch } from 'primevue';


const clicked = () => {
    console.log('Button clicked!');
};

const visible = ref(false);

const products = ref(
    [
        { id: '1000', name: 'Apple iPhone 14 Pro', category: 'Smartphone', quantity: 10 },
        { id: '1001', name: 'Samsung Galaxy S22 Ultra', category: 'Smartphone', quantity: 5 },
        { id: '1002', name: 'Google Pixel 6 Pro', category: 'Smartphone', quantity: 8 },
        { id: '1003', name: 'OnePlus 9 Pro', category: 'Smartphone', quantity: 12 },
        { id: '1004', name: 'Sony Xperia 1 III', category: 'Smartphone', quantity: 7 }
    ]
);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout title="Dashboard">
        <div class="py-12">
            <div class="mx-auto sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-surface-800"
                >
                    <div class="p-6 text-surface-900 dark:text-surface-100">
                        You're logged in!
                    </div>

                    <div class="p-4 flex">
                        <Chip label="Action" />
                    </div>

                    <div class="p-4 flex gap-2">
                        <DataTable :value="products" tableStyle="min-width: 50rem">
                            <Column field="id" header="Code" sortable></Column>
                            <Column field="name" header="Name" sortable></Column>
                            <Column field="category" header="Category"></Column>
                            <Column field="quantity" header="Quantity"></Column>
                            <Column
                                field="status"
                                header="Status"
                            >
                                <template #body="{ data }">
                                    <ToggleSwitch
                                        v-model="data.status"
                                    />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                    <div class="p-4">
                        <Button label="Button CTA" size="small" @click="visible = true" />
                    </div>

                    <div class="p-4 flex">
                        <Card style="width: 25rem; overflow: hidden">
                            <template #header>
                                <div class="">123</div>
                            </template>
                            <template #title>Advanced Card</template>
                            <template #subtitle>Card subtitle</template>
                            <template #content>
                                <p class="m-0">
                                    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Inventore sed consequuntur error repudiandae numquam deserunt quisquam repellat libero asperiores earum nam nobis, culpa ratione quam perferendis esse, cupiditate neque
                                    quas!
                                </p>
                            </template>
                            <template #footer>
                                <div class="flex gap-4 w-full">
                                    <Button label="Cancel" severity="secondary" outlined class="w-full" />
                                    <Button label="Button CTA" class="w-full" />
                                </div>
                            </template>
                        </Card>
                    </div>
                </div>
            </div>
        </div>

        <Dialog v-model:visible="visible" modal header="Edit Profile" :style="{ width: '25rem' }">
            <span class="text-surface-500 dark:text-surface-400 block mb-8">Update your information.</span>
            <div class="flex items-center gap-4 mb-4">
                <label for="username" class="font-semibold w-24">Username</label>
                <InputText id="username" class="flex-auto" autocomplete="off" />
            </div>
            <div class="flex items-center gap-4 mb-8">
                <label for="email" class="font-semibold w-24">Email</label>
                <InputText id="email" class="flex-auto" autocomplete="off" />
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button>
                        <template #default>
                            <span>Download</span>
                            <IconDownload size="16px"/>
                        </template>
                    </Button>
                    <Button type="button" label="Cancel" severity="secondary" @click="visible = false"></Button>
                    <Button type="button" label="Button" @click="visible = false"></Button>
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
