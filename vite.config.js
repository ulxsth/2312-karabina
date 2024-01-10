import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/scss/*.scss','resources/css/*.css', 'resources/js/*.js'],
            refresh: true,
        }),
    ],
    server: {
        host: true,
        hmr: {
            host: 'localhost',
        },
    },
});
