import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/favicons/favicon.ico',
                'resources/favicons/favicon-16x16.png',
                'resources/favicons/favicon-32x32.png',
                'resources/favicons/android-chrome-192x192.png',
                'resources/favicons/android-chrome-512x512.png',
                'resources/favicons/apple-touch-icon.png',
                'resources/favicons/site.webmanifest'
            ],
            refresh: [
                'resources/views/**/*.blade.php',
                'resources/views/livewire/**/*.blade.php',
                'app/Http/Livewire/**/*.php',
                'app/Livewire/**/*.php',  // For Livewire 3
                'resources/css/**',
                'resources/js/**'
            ],
        }),
        tailwindcss(),
    ],
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});