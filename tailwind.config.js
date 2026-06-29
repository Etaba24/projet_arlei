import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
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
            keyframes: {
                'modal-pop': {
                    '0%': { opacity: '0', transform: 'scale(0.95) translateY(10px)' },
                    '100%': { opacity: '1', transform: 'scale(1) translateY(0)' },
                },
                'fade-in': {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                }
            },
            animation: {
                'modal-pop': 'modal-pop 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                'fade-in': 'fade-in 0.3s ease-out forwards',
            }
        },
    },

    plugins: [forms],
};
