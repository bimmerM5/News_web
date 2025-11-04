<?php
namespace App\Core;

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $pattern, $handler): void
    {
        $this->routes['GET'][$pattern] = $handler;
    }

    public function post(string $pattern, $handler): void
    {
        $this->routes['POST'][$pattern] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $base = (require __DIR__ . '/../Config/config.php')['app']['base_url'];
        if ($base && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
            if ($path === false) { $path = '/'; }
        }
        if ($path === '') { $path = '/'; }

        foreach ($this->routes[$method] as $pattern => $handler) {
            $regex = '#^' . $pattern . '$#';
            if (preg_match($regex, $path, $matches)) {
                array_shift($matches);
                if (is_array($handler)) {
                    [$class, $methodName] = $handler;
                    $controller = new $class();
                    call_user_func_array([$controller, $methodName], $matches);
                    return;
                } else {
                    call_user_func_array($handler, $matches);
                    return;
                }
            }
        }
        http_response_code(404);
        echo '404 Not Found';
    }
}
