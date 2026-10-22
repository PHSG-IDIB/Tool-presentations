<?php

class Application {
    private string $baseContentDir;
    private string $requestUri;

    public function __construct() {
        // Pfad bereinigen
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->requestUri = trim($uri, '/');

        // Content-Verzeichnis ermitteln (mit Fallback auf Demo)
        $dir = realpath(__DIR__ . '/../content');
        if (!$dir || count(scandir($dir)) <= 2) {
            $dir = realpath(__DIR__ . '/../content-example');
        }
        $this->baseContentDir = $dir;
    }

    public function run(): void {
        $targetPath = $this->requestUri ? realpath($this->baseContentDir . '/' . $this->requestUri) : $this->baseContentDir;
        $potentialMdFile = realpath($this->baseContentDir . '/' . $this->requestUri . '.md');

        // Sicherheits-Check gegen Directory Traversal (Pfad muss im baseContentDir liegen)
        if ($potentialMdFile && strpos($potentialMdFile, $this->baseContentDir) === 0 && is_file($potentialMdFile)) {
            $this->handlePresentation($potentialMdFile);
        } elseif ($targetPath && strpos($targetPath, $this->baseContentDir) === 0 && is_dir($targetPath)) {
            $this->handleDirectory($targetPath);
        } else {
            $this->send404();
        }
    }

    private function handlePresentation(string $filePath): void {
        $markdown = file_get_contents($filePath);
        $dirPath = dirname($filePath);

        $configLoader = new ConfigLoader();
        $configData = $configLoader->load($dirPath);

        $processor = new MarkdownProcessor();

        try {
            // Wir übergeben nun auch die Config-Daten
            $processedMarkdown = $processor->process($markdown, $_GET, $configData);

            $renderer = new Renderer();
            $renderer->renderPresentation($processedMarkdown);
        } catch (Exception $e) {
            $this->send403($e->getMessage());
        }
    }

    private function handleDirectory(string $dirPath): void {
        $renderer = new Renderer();
        $renderer->renderDirectoryListing($dirPath, $this->requestUri);
    }

    private function send404(): void {
        http_response_code(404);
        echo "<h1>404 - Nicht gefunden</h1><p>Die angeforderte Ressource existiert nicht.</p>";
        exit;
    }

    private function send403(string $message): void {
        http_response_code(403);
        echo "<h1>403 - Zugriff verweigert</h1><p>" . htmlspecialchars($message) . "</p>";
        exit;
    }
}
