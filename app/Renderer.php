<?php

class Renderer {

    public function renderPresentation(string $markdown): void {
        $fontsPath = __DIR__ . '/../resources/design/fonts.html';
        $fontsHtml = file_exists($fontsPath) ? file_get_contents($fontsPath) : '';
        ?>
        <!doctype html>
        <html lang="de">
        <head>
            <meta charset="utf-8">
            <title>Präsentation</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

            <!-- PHSG Webfonts (Platypi + Albert Sans) -->
            <?php echo $fontsHtml; ?>

            <!-- Unser kompiliertes CSS von Vite (inkl. tokens.css) -->
            <link rel="stylesheet" href="/build/style.css">
        </head>
        <body>
        <div class="reveal">
            <div class="slides">
                <section data-markdown data-separator="^\n---\n$" data-separator-vertical="^\n--\n$">
                        <textarea data-template>
<?php echo htmlspecialchars($markdown, ENT_NOQUOTES, 'UTF-8'); ?>
                        </textarea>
                </section>
            </div>
        </div>

        <!-- Unser kompiliertes Javascript von Vite (als Modul) -->
        <script type="module" src="/build/main.js"></script>
        </body>
        </html>
        <?php
    }

    public function renderDirectoryListing(string $dirPath, string $currentUri): void {
        echo "<!doctype html><html lang='de'><head><meta charset='utf-8'><title>Modulübersicht</title>";

        // Simples, modernes Grund-Styling (kann später ausgelagert werden)
        echo "<style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f4f9; padding: 2rem; color: #333; }
            .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
            h1 { margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 1rem; }
            ul { list-style: none; padding: 0; margin: 0; }
            li { margin-bottom: 0.5rem; }
            a { display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: #fafafa; border: 1px solid #eaeaea; border-radius: 6px; text-decoration: none; color: #2563eb; font-weight: 500; transition: all 0.2s; }
            a:hover { background: #f0f4ff; border-color: #2563eb; }
            
            /* Styling für gesperrte Präsentationen */
            .locked a { background: #f9f9f9; border-color: #eee; color: #9ca3af; pointer-events: none; }
            .badge { font-size: 0.75rem; background: #e5e7eb; color: #4b5563; padding: 0.25rem 0.5rem; border-radius: 99px; }
        </style></head><body>";

        echo "<div class='container'><h1>Modulübersicht</h1><ul>";

        $files = scandir($dirPath);
        foreach ($files as $file) {
            $fullPath = $dirPath . '/' . $file;

            // Nur Markdown-Dateien zulassen (versteckte Dateien und Ordner werden ignoriert)
            if (is_file($fullPath) && pathinfo($fullPath, PATHINFO_EXTENSION) === 'md') {
                $nameWithoutExt = pathinfo($file, PATHINFO_FILENAME);
                $link = $currentUri ? $currentUri . '/' . $nameWithoutExt : $nameWithoutExt;

                // Prüfen, ob ein Token verlangt wird
                $content = file_get_contents($fullPath);
                $isLocked = preg_match('/\{\{\s*token:\s*[a-zA-Z0-9_-]+\s*\}\}/', $content);

                if ($isLocked) {
                    echo "<li class='locked'><a href='/{$link}'>📄 {$nameWithoutExt} <span class='badge'>🔒 Gesperrt</span></a></li>";
                } else {
                    echo "<li><a href='/{$link}'>📄 {$nameWithoutExt}</a></li>";
                }
            }
        }

        echo "</ul></div></body></html>";
    }
}
