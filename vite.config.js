import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Manrope', {
                    variable: '--font-manrope',
                    weights: [400, 500, 600, 700, 800],
                    subsets: ['latin', 'cyrillic', 'cyrillic-ext'],
                }),
                bunny('PT Sans', {
                    variable: '--font-pt-sans',
                    weights: [400, 700],
                    subsets: ['latin', 'cyrillic', 'cyrillic-ext'],
                }),
                bunny('Noto Sans SC', {
                    variable: '--font-noto-sc',
                    weights: [400, 500, 700],
                    subsets: ['latin', 'chinese-simplified'],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
