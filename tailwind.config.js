import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Deep blue brand palette
                brand: {
                    50: '#eef4fd',
                    100: '#d9e6fa',
                    200: '#b3ccf4',
                    300: '#7aa6ea',
                    400: '#3e7bdd',
                    500: '#1857c4',
                    600: '#1247a5',
                    700: '#0e3888',
                    800: '#0b2a6b',
                    900: '#081c4e',
                    950: '#050e2e',
                },
                // Cyan accent
                accent: {
                    300: '#67e8f9',
                    400: '#22d3ee',
                    500: '#06b6d4',
                    600: '#0891b2',
                },
            },
        },
    },

    plugins: [forms],
};
