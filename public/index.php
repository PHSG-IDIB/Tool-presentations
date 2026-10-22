<?php
// public/index.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Simpler Autoloader für den /app Ordner
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../app/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Applikation starten
$app = new Application();
$app->run();
