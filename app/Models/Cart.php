<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Session;
use RuntimeException;

class Cart
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function items(): array
    {
        Session::start();
        $cart = Session::get('cart', array());
        $bookModel = new Book($this->database);
        $items = array();

        foreach ((array)$cart as $bookId => $quantity) {
            $book = $bookModel->findById((int)$bookId);
            $quantity = (int)$quantity;

            if (!$book || $book['status'] !== 'published' || $quantity < 1) {
                continue;
            }

            $quantity = min($quantity, (int)$book['stock_quantity']);
            if ($quantity > 0) {
                $items[] = array('book' => $book, 'quantity' => $quantity);
            }
        }
        return $items;
    }

    public function add(int $bookId): void
    {
        $book = (new Book($this->database))->findById($bookId);
        if (!$book || $book['status'] !== 'published') {
            throw new RuntimeException('Sách không tồn tại hoặc chưa được bán.');
        }
        if ((int)$book['stock_quantity'] < 1) {
            throw new RuntimeException('Sách hiện đã hết hàng.');
        }

        Session::start();
        $cart = (array)Session::get('cart', array());
        $oldQuantity = isset($cart[(string)$bookId]) ? (int)$cart[(string)$bookId] : 0;
        $cart[(string)$bookId] = min($oldQuantity + 1, (int)$book['stock_quantity']);
        Session::put('cart', $cart);
    }

    public function update(array $quantities, ?int $removeId): void
    {
        Session::start();
        $cart = (array)Session::get('cart', array());
        $bookModel = new Book($this->database);

        if ($removeId !== null) {
            unset($cart[(string)$removeId]);
        }

        foreach ($quantities as $bookId => $quantity) {
            $book = $bookModel->findById((int)$bookId);
            $quantity = (int)$quantity;

            if (!$book || $book['status'] !== 'published' || $quantity < 1) {
                unset($cart[(string)$bookId]);
                continue;
            }
            $cart[(string)$bookId] = min($quantity, (int)$book['stock_quantity']);
        }

        Session::put('cart', $cart);
    }

    public function remove(int $bookId): void
    {
        Session::start();
        $cart = (array)Session::get('cart', array());
        unset($cart[(string)$bookId]);
        Session::put('cart', $cart);
    }

    public function clear(): void
    {
        Session::start();
        Session::put('cart', array());
    }
}
