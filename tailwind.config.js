import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: [
        // Rose colors
        'bg-rose-50', 'bg-rose-100', 'bg-rose-500', 'bg-rose-600', 'bg-rose-700',
        'text-rose-500', 'text-rose-600', 'text-rose-700',
        'border-rose-100', 'border-rose-200', 'border-rose-300', 'border-rose-500',
        'hover:bg-rose-700', 'hover:text-rose-600', 'hover:text-rose-700', 'hover:border-rose-300',
        // Emerald/Green colors
        'bg-emerald-100', 'bg-emerald-500',
        'text-emerald-500', 'text-emerald-600', 'text-emerald-700',
        'bg-green-100', 'bg-green-400', 'bg-green-500',
        'text-green-500', 'text-green-600', 'text-green-700', 'text-green-800',
        // Pink colors
        'bg-pink-50', 'bg-pink-100', 'bg-pink-500', 'bg-pink-600',
        'text-pink-500', 'text-pink-600',
        'from-rose-500', 'via-pink-500', 'to-purple-500',
        'from-rose-600', 'via-pink-600', 'to-purple-600',
        'hover:from-rose-600', 'hover:via-pink-600', 'hover:to-purple-600',
        // Purple colors
        'bg-purple-100', 'bg-purple-500', 'bg-purple-600', 'bg-purple-700',
        'text-purple-500', 'text-purple-600',
        // Other utility colors
        'bg-amber-100', 'text-amber-600',
        'bg-blue-100', 'text-blue-600',
        'bg-indigo-100', 'text-indigo-600',
        // Peer checked states
        'peer-checked:border-rose-500', 'peer-checked:bg-rose-500', 'peer-checked:bg-rose-50',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', 'sans-serif'],
                display: ['Outfit', 'sans-serif'],
            },
            colors: {
                primary: {
                    50: '#fdf2f8',
                    100: '#fce7f3',
                    200: '#fbcfe8',
                    300: '#f9a8d4',
                    400: '#f472b6',
                    500: '#ec4899',
                    600: '#db2777',
                    700: '#be185d',
                    800: '#9d174d',
                    900: '#831843',
                },
                secondary: {
                    50: '#f5f3ff',
                    100: '#ede9fe',
                    200: '#ddd6fe',
                    300: '#c4b5fd',
                    400: '#a78bfa',
                    500: '#8b5cf6',
                    600: '#7c3aed',
                    700: '#6d28d9',
                    800: '#5b21b6',
                    900: '#4c1d95',
                }
            }
        },
    },

    plugins: [forms],
};
