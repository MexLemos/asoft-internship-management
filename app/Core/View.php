<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class View
{
    private static array $sections = [];
    private static ?string $currentSection = null;

    /**
     * Renders a view file within an optional layout.
     */
    public static function render(string $view, array $data = [], ?string $layout = 'admin'): string
    {
        $viewFile = __DIR__ . '/../../resources/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new RuntimeException("View [{$view}] não encontrada em [{$viewFile}].");
        }

        // Extract view data into local scope
        extract($data, EXTR_SKIP);

        // Render view content
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // If layout specified, render inside layout
        if ($layout !== null) {
            $layoutFile = __DIR__ . '/../../resources/views/layouts/' . $layout . '.php';
            if (!file_exists($layoutFile)) {
                throw new RuntimeException("Layout [{$layout}] não encontrado em [{$layoutFile}].");
            }

            ob_start();
            include $layoutFile;
            return ob_get_clean();
        }

        return $content;
    }

    /**
     * Includes a subview partial.
     */
    public static function partial(string $partial, array $data = []): string
    {
        $partialFile = __DIR__ . '/../../resources/views/partials/' . str_replace('.', '/', $partial) . '.php';
        if (!file_exists($partialFile)) {
            $partialFile = __DIR__ . '/../../resources/views/' . str_replace('.', '/', $partial) . '.php';
        }
        if (!file_exists($partialFile)) {
            return "<!-- Partial [{$partial}] não encontrado -->";
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $partialFile;
        return ob_get_clean();
    }

    public static function startSection(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }

    public static function endSection(): void
    {
        if (self::$currentSection === null) {
            throw new RuntimeException('Nenhuma seção aberta para fechar.');
        }
        self::$sections[self::$currentSection] = ob_get_clean();
        self::$currentSection = null;
    }

    public static function yieldSection(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }
}
