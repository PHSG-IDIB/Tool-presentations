<?php

class MarkdownProcessor {

    /**
     * Verarbeitet den rohen Markdown-Text.
     * Wirft eine Exception, wenn ein Token verlangt wird, aber ungültig ist.
     */
    public function process(string $markdown, array $queryParams, array $config = []): string {
        $markdown = $this->checkAccessAndStripToken($markdown, $queryParams);
        $markdown = $this->parseCustomTags($markdown);
        $markdown = $this->injectConfigVariables($markdown, $config);

        return $markdown;
    }

    private function injectConfigVariables(string $markdown, array $config): string {
        // Findet: {{ datum }}, {{ datum + 7 }}, {{ datum - 14 }}
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_-]+)\s*(?:([+-])\s*(\d+))?\s*\}\}/', function($matches) use ($config) {
            $key = $matches[1];

            // Falls der Key in der JSON nicht existiert, Text unverändert lassen
            if (!isset($config[$key])) {
                return $matches[0];
            }

            $value = $config[$key];

            // Prüfen, ob eine Rechenoperation (+ 7) mitgegeben wurde
            if (!empty($matches[2]) && isset($matches[3])) {
                $operator = $matches[2];
                $amount = (int)$matches[3];

                // 1. Fall: Datum im Format 21.10.2026
                if (preg_match('/^\d{1,2}\.\d{1,2}\.\d{4}$/', $value)) {
                    $date = DateTime::createFromFormat('d.m.Y', $value);
                    if ($date) {
                        $date->modify("{$operator}{$amount} days");
                        $value = $date->format('d.m.Y');
                    }
                }
                // 2. Fall: Datum im ISO-Format 2026-10-21
                elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    $date = DateTime::createFromFormat('Y-m-d', $value);
                    if ($date) {
                        $date->modify("{$operator}{$amount} days");
                        $value = $date->format('d.m.Y'); // Ausgabe immer als CH-Datum
                    }
                }
                // 3. Fall: Normale Zahl (z.B. Woche)
                elseif (is_numeric($value)) {
                    $value = $operator === '+' ? ($value + $amount) : ($value - $amount);
                }
            }

            return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        }, $markdown);
    }

    private function checkAccessAndStripToken(string $markdown, array $queryParams): string {
        if (preg_match('/\{\{\s*token:\s*([a-zA-Z0-9_-]+)\s*\}\}/', $markdown, $matches)) {
            $requiredToken = $matches[1];
            $providedToken = $queryParams['access'] ?? '';

            if ($providedToken !== $requiredToken) {
                throw new Exception("Für diese Präsentation wird ein gültiger Access-Token benötigt.");
            }
            // Token aus dem Markdown entfernen
            $markdown = preg_replace('/\{\{\s*token:[^\}]+\}\}/', '', $markdown);
        }
        return $markdown;
    }

    private function parseCustomTags(string $markdown): string {
        // Slide-Typen: {{ slide: chapter-slide }} -> <!-- .slide: class="chapter-slide" -->
        $markdown = preg_replace('/\{\{\s*slide:\s*([^}]+)\s*\}\}/', '<!-- .slide: class="$1" -->', $markdown);

        // Abschnitte: {{ abschnitt: Einleitung }} -> <!-- .slide: data-abschnitt="Einleitung" -->
        $markdown = preg_replace('/\{\{\s*abschnitt:\s*([^}]+)\s*\}\}/', '<!-- .slide: data-abschnitt="$1" -->', $markdown);

        // Timer: {{ timer: 10 }} -> <div class="presentation-timer" data-minutes="10"></div>
        $markdown = preg_replace('/\{\{\s*timer:\s*(\d+)\s*\}\}/', '<div class="presentation-timer" data-minutes="$1"></div>', $markdown);

        return $markdown;
    }
}
