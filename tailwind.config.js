import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['DM Sans', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                'input': '0 1px 2px 0px rgba(9, 9, 11, 0.05)',
                'dialog': '0 -1px 16.9px 0px rgba(0, 0, 0, 0.05)',
                'box': '0 1px 2px 0 rgba(9, 9, 11, 0.05)',
                'table': '0 -1px 16.9px 0 rgba(0, 0, 0, 0.05)',
                'dropdown': '0px 4px 8px 0 rgba(9, 9, 11, 0.05)',
            },
            fontSize: {
                xxs: ['10px', '16px'],
                xs: ['12px', '16px'],
                sm: ['14px', '20px'],
                base: ['16px', '24px'],
                lg: ['20px', '28px'],
                xl: ['24px', '32px'],
                xxl: ['30px', '42px'],
            },
        },
    },

    plugins: [forms, require('tailwindcss-primeui')],
};
