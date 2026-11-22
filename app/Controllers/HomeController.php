<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Book;
use App\Models\Category;

final class HomeController extends Controller
{
    public function index(): void
    {
        $database = $this->database();
        $books = (new Book($database))->published();
        $categories = (new Category($database))->all();
        $data = array();
        $data['title'] = 'Trang chủ';
        $data['books'] = $books;
        $data['categories'] = $categories;
        $this->view->render('shop/home', $data);
    }
}
