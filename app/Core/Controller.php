<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    public function __construct(protected View $view)
    {
    }

    protected function database(): Database
    {
        return new Database(require dirname(__DIR__, 2) . '/config/database.php');
    }
}
