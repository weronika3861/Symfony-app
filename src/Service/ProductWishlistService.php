<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\ProductRepository;
use App\Repository\ProductWishlistRepositoryInterface;

class ProductWishlistService
{
    private ProductWishlistRepositoryInterface $wishlistRepository;
    private ProductRepository $productRepository;

    public function __construct(
        ProductWishlistRepositoryInterface $wishlistRepository,
        ProductRepository $productRepository
    ) {
        $this->wishlistRepository = $wishlistRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @param int $productId
     */
    public function addByProductId(int $productId): void
    {
        $this->wishlistRepository->addByProductId($productId);
    }

    /**
     * @return ?[]Product
     */
    public function getProducts(): ?array
    {
        $wishlistProductIds = $this->wishlistRepository->getProductIds();

        return $this->productRepository->findBy(['id' => $wishlistProductIds]);
    }
}
