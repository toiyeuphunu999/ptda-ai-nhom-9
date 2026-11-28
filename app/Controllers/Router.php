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
        foreach ($this->routes as $route) {
            $routeMethod = $route[0];
            $pattern = $route[1];
            $handler = $route[2];

            if ($method !== $routeMethod) {
                continue;
            }

            $regex = $this->makeRegex($pattern);

            if (preg_match('#^' . $regex . '$#', $path, $matches) === 1) {
                $params = $this->getRouteParameters($matches);
                call_user_func_array($handler, $params);
                return;
            }
        }

        $this->showNotFound();
    }

    private function makeRegex(string $pattern): string
    {
        $regex = preg_replace_callback(
                '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
                function (array $match): string {
                    $name = $match[1];
                    $isId = preg_match('/(?:^|_)id$/', $name) === 1;

                    if ($isId) {
                        return '(?P<' . $name . '>[1-9][0-9]*)';
                    }

                    return '(?P<' . $name . '>[a-zA-Z0-9-]+)';
                },
                $pattern
            );

        if (!is_string($regex)) {
            return preg_quote($pattern, '#');
        }

        return $regex;
    }

    private function getRouteParameters(array $matches): array
    {
        $params = array();

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[] = $value;
            }
        }

        return $params;
    }

    private function showNotFound(): void
    {
        http_response_code(404);

        $viewPath = dirname(__DIR__) . '/Views';
        $layoutPath = $viewPath . '/layouts';
        $view = new View($viewPath, $layoutPath);

        $view->render('errors/error', array(
            'title' => 'Không tìm thấy trang',
            'errorCode' => '404',
            'errorTitle' => 'Không tìm thấy trang',
            'errorMessage' => 'Đường dẫn bạn truy cập không tồn tại trên website.',
            'errorLabel' => 'Hãy kiểm tra lại đường dẫn hoặc quay về trang chủ để tiếp tục.',
        ));
    }
}
