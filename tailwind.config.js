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
        // Teal colors (brand primary)
        'bg-teal-50', 'bg-teal-100', 'bg-teal-500', 'bg-teal-600', 'bg-teal-700',
        'text-teal-500', 'text-teal-600', 'text-teal-700',
        'border-teal-100', 'border-teal-200', 'border-teal-300', 'border-teal-500',
        'hover:bg-teal-700', 'hover:text-teal-600', 'hover:text-teal-700', 'hover:border-teal-300',
        'shadow-teal-100/50',
        // Emerald/Green colors
        'bg-emerald-100', 'bg-emerald-500',
        'text-emerald-500', 'text-emerald-600', 'text-emerald-700',
        'bg-green-100', 'bg-green-400', 'bg-green-500',
        'text-green-500', 'text-green-600', 'text-green-700', 'text-green-800',
        // Cyan colors (brand mid-gradient)
        'bg-cyan-50', 'bg-cyan-100', 'bg-cyan-500', 'bg-cyan-600',
        'text-cyan-500', 'text-cyan-600',
        'border-cyan-100', 'border-cyan-200', 'border-cyan-300',
        'shadow-cyan-100/50',
        'from-teal-500', 'via-cyan-500', 'to-violet-500',
        'from-teal-600', 'via-cyan-600', 'to-violet-600',
        'hover:from-teal-600', 'hover:via-cyan-600', 'hover:to-violet-600',
        // Violet colors (brand accent)
        'bg-violet-100', 'bg-violet-500', 'bg-violet-600', 'bg-violet-700',
        'text-violet-500', 'text-violet-600',
        'border-violet-100', 'border-violet-200', 'border-violet-300',
        'shadow-violet-100/50',
        // Other utility colors
        'bg-amber-100', 'text-amber-600',
        'bg-blue-100', 'text-blue-600',
        'bg-indigo-100', 'text-indigo-600',
        // Peer checked states
        'peer-checked:border-teal-500', 'peer-checked:bg-teal-500', 'peer-checked:bg-teal-50',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', 'sans-serif'],
                display: ['Outfit', 'sans-serif'],
            },
            colors: {
                primary: {
                    50: '#f0fdfa',
                    100: '#ccfbf1',
                    200: '#99f6e4',
                    300: '#5eead4',
                    400: '#2dd4bf',
                    500: '#14b8a6',
                    600: '#0d9488',
                    700: '#0f766e',
                    800: '#115e59',
                    900: '#134e4a',
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
