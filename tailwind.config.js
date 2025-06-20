import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
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
            typography: ({ theme }) => ({
                DEFAULT: {
                    css: {
                        '--tw-prose-counters': theme('colors.gray[500]'),
                        h1: {
                            fontSize: '24px',
                            lineHeight: '32px',
                            margin: '0 auto',
                        },
                        h2: {
                            fontSize: '20px',
                            lineHeight: '28px',
                            margin: '0 auto',
                        },
                        p: {
                            fontSize: '14px',
                            lineHeight: '20px',
                            margin: '0 auto',
                        },
                        'li > p': {
                            marginTop: '0',
                            marginBottom: '0',
                        },
                        li: {
                            marginTop: '0',
                            marginBottom: '0',
                        },
                        maxWidth: '100ch',
                    },
                },
            }),
            boxShadow: {
                'input': '0 1px 2px 0px rgba(9, 9, 11, 0.05)',
                'dialog': '0 -1px 16.9px 0px rgba(0, 0, 0, 0.05)',
                'box': '0 1px 2px 0 rgba(9, 9, 11, 0.05)',
                'table': '0 -1px 16.9px 0 rgba(0, 0, 0, 0.05)',
                'dropdown': '0px 4px 8px 0 rgba(9, 9, 11, 0.05)',
                'toast': '0 4px 20px 0 rgba(12, 17, 29, 0.08)',
                'item': '0px -1px 16.9px 0px rgba(0, 0, 0, 0.05)',
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

    plugins: [forms, require('tailwindcss-primeui'), typography],
};
