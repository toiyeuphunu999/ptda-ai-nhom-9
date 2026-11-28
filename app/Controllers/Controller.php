<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    protected function database(): Database
    {
        $configFile = dirname(__DIR__, 2) . '/config/database.php';
        $config = require $configFile;

        return new Database($config);
    }
}
