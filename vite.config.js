import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/pages/login.css',
                'resources/js/app.js',
                'resources/css/pages/dashboard.css',
                'resources/css/pages/schedule.css',
                'resources/css/pages/orders.css',
                'resources/css/pages/dispatch.css',
                'resources/css/pages/assignments.css',
                'resources/css/pages/inventory.css',
                'resources/css/pages/analytics.css',
                'resources/css/pages/sales.css',
                'resources/css/pages/settings.css',
                'resources/css/pages/employees.css',
                'resources/css/pages/login.css',
                'resources/js/pages/dashboard.js',
                'resources/js/pages/schedule.js',
                'resources/js/pages/orders.js',
                'resources/js/pages/dispatch.js',
                'resources/js/pages/assignments.js',
                'resources/js/pages/inventory.js',
                'resources/js/pages/analytics.js',
                'resources/js/pages/sales.js',
                'resources/js/pages/settings.js',
                'resources/js/pages/employees.js',
                'resources/js/pages/products.js',
                'resources/js/pages/stock-in.js',
                'resources/js/pages/stock-out.js',
                'resources/js/pages/order-progress.js',
                'resources/js/pages/returns.js',
                'resources/js/pages/notifications.js',
                'resources/js/pages/assignments-employee.js',
                'resources/js/pages/login.js',
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
