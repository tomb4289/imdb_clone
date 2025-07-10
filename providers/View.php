<?php
namespace App\Providers;

class View
{
    static public function render(string $template, array $data = []): string
    {
        extract($data);
        ob_start();
        include(__DIR__ . '/../views/' . $template . '.php');
        return ob_get_clean();
    }

    static public function redirect(string $path): void
    {
        header('Location: ' . BASE . '/' . $path);
        exit();
    }
}