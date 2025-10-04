<?php
namespace App\Core;

class Controller
{
    protected function view(string $template, array $data = []): void
    {
        extract($data);
        $baseUrl = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        include __DIR__ . '/../Views/layout/header.php';
        include __DIR__ . '/../Views/' . $template . '.php';
        include __DIR__ . '/../Views/layout/footer.php';
    }

    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
