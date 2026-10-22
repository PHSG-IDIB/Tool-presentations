// vite.config.js
import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    // Wir definieren den Ordner mit den Rohdaten
    root: 'resources',

    build: {
        // Wohin soll Vite die fertigen Dateien legen?
        outDir: '../public/build',
        // Den Ordner vor jedem Build leeren
        emptyOutDir: true,
        rollupOptions: {
            // Unsere Einstiegspunkte
            input: {
                main: path.resolve(__dirname, 'resources/js/main.js'),
                style: path.resolve(__dirname, 'resources/css/style.css')
            },
            output: {
                // Fixe Dateinamen ohne Hash (z.B. main.js statt main-a3f8b.js)
                // Das macht es für den PHP-Renderer einfacher
                entryFileNames: '[name].js',
                assetFileNames: '[name].[ext]'
            }
        }
    }
});
