<?php
declare(strict_types=1);

namespace App\Repository;

use App\Exception\EmptyWishlistException;
use App\Exception\WishlistNotContainProductException;

interface ProductWishlistRepositoryInterface
{
    /**
     * @param int $productId
     */
    public function addByProductId(int $productId): void;

    /**
     * @return ?[]int
     */
    public function getProductIds(): ?array;

    /**
     * @param int $productId
     * @throws EmptyWishlistException
     * @throws WishlistNotContainProductException
     */
    public function deleteByProductId(int $productId): void;

    /**
     * @throws EmptyWishlistException
     */
    public function deleteAllProductIds(): void;
}
