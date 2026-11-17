<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [$method, $pattern, $handler];
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as [$routeMethod, $pattern, $handler]) {
            if ($method !== $routeMethod) {
                continue;
            }

            $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[a-zA-Z0-9-]+)', $pattern);
            if (preg_match('#^' . $regex . '$#', $path, $matches) === 1) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $handler(...array_values($params));
                return;
            }
        }

        http_response_code(404);
        (new View(dirname(__DIR__) . '/Views', dirname(__DIR__) . '/Views/layouts'))
            ->render('errors/error', [
                'title' => 'Không tìm thấy trang',
                'errorCode' => '404',
                'errorTitle' => 'Không tìm thấy trang',
                'errorMessage' => 'Đường dẫn bạn truy cập không tồn tại trên website.',
                'errorLabel' => 'Hãy kiểm tra lại đường dẫn hoặc quay về trang chủ để tiếp tục.',
            ]);
    }
}
