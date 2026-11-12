<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Book;
use App\Models\Category;

final class HomeController extends Controller
{
    public function index(): void
    {
        $database = new Database(require dirname(__DIR__, 2) . '/config/database.php');
        $books = (new Book($database))->published();
        $categories = (new Category($database))->all();
        $this->view->render('shop/home', compact('books', 'categories') + ['title' => 'Trang chủ']);
    }
}
