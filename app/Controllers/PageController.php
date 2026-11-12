<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class PageController extends Controller
{
    public function show(string $page): void { $this->view->render('shop/' . $page, ['title'=>ucfirst($page)]); }
}
