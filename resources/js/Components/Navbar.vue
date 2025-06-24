<script setup>
import {isDark, sidebarState, toggleDarkMode} from '@/Composables'
import {
    IconWorld,
    IconLogout,
    IconMenu2,
    IconMoon,
    IconSun
} from '@tabler/icons-vue';
import {Link, router, usePage} from "@inertiajs/vue3";
import { Button, Popover } from "primevue";
import {ref} from "vue";
import {loadLanguageAsync} from "laravel-vue-i18n";

defineProps({
    title: String
})

const op = ref();
const toggle = (event) => {
    op.value.toggle(event);
}

const currentLocale = ref(usePage().props.locale);
const locales = JSON.parse(usePage().props.availableLocales);

const changeLanguage = async (langVal) => {
    try {
        op.value.toggle(false)
        currentLocale.value = langVal;
        await loadLanguageAsync(langVal);
        await axios.get(`/locale/${langVal}`);
    } catch (error) {
        console.error('Error changing locale:', error);
    }
};

const handleLogOut = () => {
    router.post(route('logout'))
}
</script>

<template>
    <nav
        aria-label="secondary"
        class="sticky top-0 z-20 py-2 px-3 md:px-5 bg-surface-50 dark:bg-surface-950 flex items-center gap-3 transition-colors duration-200 w-full"
    >
        <div class="w-full flex justify-between">
            <Button
                type="button"
                severity="secondary"
                rounded
                outlined
                class="shrink-0"
                @click="sidebarState.isOpen = !sidebarState.isOpen"
            >
                <template #icon>
                    <IconMenu2 size="18" stroke-width="1.5" />
                </template>
            </Button>
            <div class="flex items-center justify-end w-full">
                <Button
                    type="button"
                    severity="secondary"
                    rounded
                    variant="text"
                    @click="() => { toggleDarkMode() }"
                >
                    <template #icon>
                        <IconSun v-if="!isDark" size="20" stroke-width="1.5" />
                        <IconMoon v-if="isDark" size="20" stroke-width="1.5" />
                    </template>
                </Button>
                <Button
                    type="button"
                    severity="secondary"
                    rounded
                    variant="text"
                    @click="toggle"
                >
                    <template #icon>
                        <IconWorld size="20" stroke-width="1.5" />
                    </template>
                </Button>
                <Button
                    type="button"
                    severity="secondary"
                    rounded
                    variant="text"
                    @click="handleLogOut"
                >
                    <template #icon>
                        <IconLogout size="20" stroke-width="1.5" />
                    </template>
                </Button>
            </div>
        </div>
    </nav>

    <Popover unstyled ref="op">
        <div class="py-2 flex flex-col items-center w-[120px] bg-white">
            <div
                v-for="locale in locales"
                class="p-3 flex items-center gap-3 self-stretch text-sm hover:bg-gray-100 hover:cursor-pointer"
                :class="{'bg-primary-50 text-primary-500': locale.value === currentLocale}"
                @click="changeLanguage(locale.value)"
            >
                {{ locale.label }}
            </div>
        </div>
    </Popover>
</template>
