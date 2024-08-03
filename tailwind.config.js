import preset from './vendor/filament/support/tailwind.config.preset';
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './app/Livewire/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './resources/**/*.{js,cjs,mjs,ts,vue,scss,css}'
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                'montserrat': ['Montserrat', ...defaultTheme.fontFamily.sans],
            },
            minHeight: {
                '80': '20rem'
            },
            colors: {
                'lightgray': '#f8f9fb',
                'mildgray': '#f2f3f5',
                'mc-green': '#26960f',
                'mc-purple': '#6a62e1',
                'mc-yellow': '#fec106',
                'mc-lightgreen': '#b3df72',
                'mc-lightpurple': '#9f8fef'
            }
        },
    },

    plugins: [forms],
};
