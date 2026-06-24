import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            // Only watch actual source Blade files, not auto-generated storage/framework files.
            // Watching all files causes an infinite reload loop because Livewire constantly
            // regenerates cached view/class files in storage/, which triggers Vite's watcher.
            refresh: [
                'resources/views/**',
                'app/**',
                'routes/**',
            ],
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        hmr: {
            host: 'localhost',
        },
        watch: {
            // Exclude storage directories from file watching entirely
            ignored: [
                '**/storage/**',
                '**/vendor/**',
                '**/.git/**',
            ],
            usePolling: false,
        },
    },
});
