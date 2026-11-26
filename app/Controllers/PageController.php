<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class PageController extends Controller
{
    public function show(string $page): void
    {
        $title = ucfirst($page);
        $this->view->render('shop/' . $page, array('title' => $title));
    }
    public function about(): void
    {
        $this->show('about');
    }

    public function contact(): void
    {
        $this->show('contact');
    }

    public function faq(): void
    {
        $this->show('faq');
    }

    public function terms(): void
    {
        $this->show('terms');
    }

    public function privacy(): void
    {
        $this->show('privacy');
    }

    public function shipping(): void
    {
        $this->show('shipping');
    }

    public function returns(): void
    {
        $this->show('returns');
    }

    public function guide(): void
    {
        $this->show('guide');
    }
}
