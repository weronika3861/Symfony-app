<?php
declare(strict_types=1);

namespace App\Repository;

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
}
