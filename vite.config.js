import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/pages/dashboard.css',
                'resources/css/pages/schedule.css',
                'resources/css/pages/orders.css',
                'resources/css/pages/dispatch.css',
                'resources/css/pages/assignments.css',
                'resources/css/pages/inventory.css',
                'resources/css/pages/analytics.css',
                'resources/css/pages/settings.css',
                'resources/css/pages/employees.css',
                'resources/js/pages/dashboard.js',
                'resources/js/pages/schedule.js',
                'resources/js/pages/orders.js',
                'resources/js/pages/dispatch.js',
                'resources/js/pages/assignments.js',
                'resources/js/pages/inventory.js',
                'resources/js/pages/analytics.js',
                'resources/js/pages/settings.js',
                'resources/js/pages/employees.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
