<?php

class ConfigLoader {

    public function load(string $directory): array {
        $config = [];
        $yamlFile = $directory . '/config.yaml';
        $jsonFile = $directory . '/config.json';

        // 1. Priorität: JSON (falls du das bevorzugst, da PHP es nativ kann)
        if (file_exists($jsonFile)) {
            $json = file_get_contents($jsonFile);
            return json_decode($json, true) ?: [];
        }

        // 2. Priorität: YAML
        if (file_exists($yamlFile)) {
            // Wenn die PHP YAML-Erweiterung installiert ist
            if (function_exists('yaml_parse_file')) {
                return yaml_parse_file($yamlFile) ?: [];
            }

            // Simpler Fallback für grundlegende "key: value" YAML-Dateien ohne Abhängigkeiten
            $lines = file($yamlFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Kommentare ignorieren
                if (strpos(trim($line), '#') === 0) continue;

                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    // Anführungszeichen entfernen, falls vorhanden
                    $value = trim($value, '"\'');
                    $config[$key] = $value;
                }
            }
        }

        return $config;
    }
}
