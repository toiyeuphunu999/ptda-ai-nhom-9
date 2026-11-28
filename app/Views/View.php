<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    private string $viewPath;
    private string $layoutPath;

    public function __construct(string $viewPath, string $layoutPath)
    {
        $this->viewPath = $viewPath;
        $this->layoutPath = $layoutPath;
    }

    public function render(string $view, array $data = [], ?string $layout = null): void
    {
        $contentView = $this->viewPath . '/' . $view . '.php';
        $layoutFile = $this->getLayoutFile($layout);

        extract($data, EXTR_SKIP);
        Session::start();
        $currentUser = Session::get('user');
        $cart = (array)Session::get('cart', array());
        $cartCount = 0;
        foreach ($cart as $quantity) {
            $cartCount += (int)$quantity;
        }
        $successMessage = Session::pullFlash('success');
        $failureMessage = Session::pullFlash('failure');
        ob_start();
        require $contentView;
        $content = (string) ob_get_clean();
        ob_start();
        require dirname(__DIR__) . '/Views/partials/flash.php';
        $content = (string)ob_get_clean() . $content;
        require $layoutFile;
    }

    private function getLayoutFile(?string $layout): string
    {
        if ($layout === 'admin') {
            return $this->layoutPath . '/admin.php';
        }

        if ($layout === 'auth') {
            return $this->layoutPath . '/auth.php';
        }

        return $this->layoutPath . '/shop.php';
    }
}
