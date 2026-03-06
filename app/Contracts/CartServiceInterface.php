<?php

namespace App\Contracts;

use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;

interface CartServiceInterface
{
    public function current(): Cart;

    public function add(int $productId, int $quantity = 1, ?int $variantId = null): CartItem;

    public function update(int $cartItemId, int $quantity): void;

    public function remove(int $cartItemId): void;

    public function clear(): void;

    public function mergeSessionIntoCustomer(int $customerId): void;

    public function count(): int;
}
