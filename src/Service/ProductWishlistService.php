<?php
declare(strict_types=1);

namespace App\Service;

use App\Exception\EmptyWishlistException;
use App\Exception\ProductAlreadyInWishlistException;
use App\Exception\ProductLimitExceededException;
use App\Exception\ProductNotExistException;
use App\Exception\WishlistNotContainProductException;
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
     * @throws ProductAlreadyInWishlistException
     * @throws ProductLimitExceededException
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

    /**
     * @param int $productId
     * @throws ProductNotExistException
     * @throws EmptyWishlistException
     * @throws WishlistNotContainProductException
     */
    public function deleteByProductId(int $productId): void
    {
        if (!$this->productRepository->find($productId)) {
            throw new ProductNotExistException('This product does not exist.');
        }

        $this->wishlistRepository->deleteByProductId($productId);
    }

    /**
     * @throws EmptyWishlistException
     */
    public function deleteAllProductIds(): void
    {
        $this->wishlistRepository->deleteAllProductIds();
    }
}
