<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public function __construct(private string $viewPath, private string $layoutPath)
    {
    }

    public function render(string $view, array $data = [], ?string $layout = null): void
    {
        $contentView = $this->viewPath . '/' . $view . '.php';
        $layoutFile = $layout === 'admin'
            ? $this->layoutPath . '/admin.php'
            : $this->layoutPath . '/shop.php';

        extract($data, EXTR_SKIP);
        ob_start();
        require $contentView;
        $content = (string) ob_get_clean();
        require $layoutFile;
    }
}
