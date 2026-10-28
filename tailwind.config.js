import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                arab: ['Amiri', 'serif'],
            },
            colors: {
                // Hijau zamrud: warna utama bernuansa Islami.
                zamrud: {
                    50: '#ECFDF5', 100: '#D1FAE5', 200: '#A7F3D0', 300: '#6EE7B7', 400: '#34D399',
                    500: '#10B981', 600: '#0E7C5A', 700: '#0B6449', 800: '#085540', 900: '#064E3B', 950: '#022C22',
                },
                // Emas: aksen.
                emas: {
                    50: '#FDF9EC', 100: '#FAF0CC', 200: '#F5E09A', 300: '#EFCB5E', 400: '#E3B53A',
                    500: '#D4A72C', 600: '#B48420', 700: '#8F631C', 800: '#764F1D', 900: '#64421D',
                },
                // Gading: latar.
                gading: { 50: '#FBF8F1', 100: '#F5EFE0', 200: '#EADFC4' },
            },
            backgroundImage: {
                // Pola bintang delapan (rub el hizb) yang samar.
                pola: "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48' viewBox='0 0 48 48'%3E%3Cg fill='none' stroke='%23FFFFFF' stroke-opacity='0.07' stroke-width='1'%3E%3Crect x='14' y='14' width='20' height='20'/%3E%3Crect x='14' y='14' width='20' height='20' transform='rotate(45 24 24)'/%3E%3Ccircle cx='24' cy='24' r='4'/%3E%3C/g%3E%3C/svg%3E\")",
                'pola-terang': "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48' viewBox='0 0 48 48'%3E%3Cg fill='none' stroke='%23064E3B' stroke-opacity='0.05' stroke-width='1'%3E%3Crect x='14' y='14' width='20' height='20'/%3E%3Crect x='14' y='14' width='20' height='20' transform='rotate(45 24 24)'/%3E%3Ccircle cx='24' cy='24' r='4'/%3E%3C/g%3E%3C/svg%3E\")",
            },
        },
    },

    plugins: [forms],
};
