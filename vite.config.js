import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        // The Laravel dev server sometimes binds to the machine's LAN IP
        // instead of 127.0.0.1 (e.g. when 127.0.0.1:8000 is already taken
        // by another project) — Vite's dev server otherwise rejects asset
        // requests from that origin as cross-origin, silently breaking
        // every Alpine.js-powered dropdown on the page.
        cors: true,
    },
});
