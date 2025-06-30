<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import {Link} from '@inertiajs/vue3';
import dayjs from "dayjs";
import {IconMoon, IconSun, IconWorld} from "@tabler/icons-vue";
import {Button, Menu} from "primevue";
import {isDark, toggleDarkMode} from "@/Composables/index.js";
import {ref} from "vue";
import {loadLanguageAsync} from "laravel-vue-i18n";

const menu = ref();
const locales = ref([
    {
        label: 'English',
        command: () => {
            changeLanguage('en');
        }
    },
    {
        label: '中文',
        command: () => {
            changeLanguage('cn')
        }
    }
]);

const toggle = (event) => {
    menu.value.toggle(event);
};

const changeLanguage = async (langVal) => {
    try {
        await loadLanguageAsync(langVal);
        await axios.get(`/locale/${langVal}`);
    } catch (error) {
        console.error('Error changing locale:', error);
    }
};
</script>

<template>
    <div class="flex py-3 px-5 md:px-10 justify-end items-center w-full bg-surface-100 dark:bg-surface-950 transition-all duration-200">
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
        </div>
    </div>
    <div
        class="flex min-h-screen flex-col gap-8 items-center bg-surface-100 pt-6 sm:justify-center dark:bg-surface-950 transition-all duration-200"
    >
        <div class="flex flex-col items-center self-stretch">
            <Link href="/">
                <ApplicationLogo
                    width="80"
                />
            </Link>

            <div class="flex flex-col items-center self-stretch w-full transition-colors duration-200">
                <span class="text-lg md:text-xl font-bold text-surface-950 dark:text-white">{{ $t('public.wondering_seafood') }}</span>
                <span class="font-semibold text-surface-500">{{ $t('public.admin_portal') }}</span>
            </div>
        </div>

        <div class="flex flex-col justify-center items-center pb-12 md:px-8 xs:gap-y-[60px] w-full">
            <div class="w-full flex justify-center">
                <div class="bg-white dark:bg-surface-900 p-5 rounded-xl shadow-dialog w-full max-w-xs md:max-w-none md:w-[360px] flex flex-col justify-center items-center mx-5">
                    <slot />
                </div>
            </div>
        </div>

        <div class="text-center text-surface-500 text-xs">© {{ dayjs().year() }} Wondering Seafood. All rights reserved.</div>
    </div>

    <Menu
        ref="menu"
        id="overlay_menu"
        :model="locales"
        :popup="true"
    />
</template>
