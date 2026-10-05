<?php

declare(strict_types=1);

namespace Team\Shared\View;

final class TemplateRenderer
{
    public function __construct(
        private readonly string $templatesPath,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): string
    {
        $path = $this->templatesPath . '/' . ltrim($template, '/');

        if (!is_file($path)) {
            throw new \RuntimeException('Template not found: ' . $template);
        }

        extract($data, EXTR_SKIP);

        ob_start();

        try {
            require $path;

            return (string) ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }
    }
}
