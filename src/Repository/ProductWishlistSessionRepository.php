<?php
declare(strict_types=1);

namespace App\Repository;

use App\Exception\EmptyWishlistException;
use App\Exception\ProductAlreadyInWishlistException;
use App\Exception\ProductLimitExceededException;
use App\Exception\WishlistNotContainProductException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ProductWishlistSessionRepository implements ProductWishlistRepositoryInterface
{
    private const LIMIT_OF_PRODUCTS = 10;
    private const SESSION_ATTRIBUTE_NAME = 'wishlistProductIds';

    private Session $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param int $productId
     * @throws ProductAlreadyInWishlistException
     * @throws ProductLimitExceededException
     */
    public function addByProductId(int $productId): void
    {
        if (!$wishlistProductIds = $this->getProductIds()) {
            $wishlistProductIds = [];
        }

        if ($this->productAlreadyInWishList($wishlistProductIds, $productId)) {
            throw new ProductAlreadyInWishlistException('Product is already in the wishlist.');
        }

        if ($this->productLimitExceeded($wishlistProductIds)) {
            throw new ProductLimitExceededException('There is already maximum amount of products in the wishlist.');
        }

        $wishlistProductIds[] = $productId;
        $this->session->set(self::SESSION_ATTRIBUTE_NAME, $wishlistProductIds);
    }

    /**
     * @return ?[]int
     */
    public function getProductIds(): ?array
    {
        return $this->session->get(self::SESSION_ATTRIBUTE_NAME);
    }

    /**
     * @param int $productId
     * @throws EmptyWishlistException
     * @throws WishlistNotContainProductException
     */
    public function deleteByProductId(int $productId): void
    {
        if (!$wishlistProductIds = $this->getProductIds()) {
            throw new EmptyWishlistException('You cannot delete products, the wishlist is empty.');
        }

        if (!$this->productAlreadyInWishList($wishlistProductIds, $productId)) {
            throw new WishlistNotContainProductException('Wishlist does not contain this product.');
        }

        $this->removeProductIdFromArray($wishlistProductIds, $productId);
        $this->session->set(self::SESSION_ATTRIBUTE_NAME, $wishlistProductIds);
    }

    /**
     * @throws EmptyWishlistException
     */
    public function deleteAllProductIds(): void
    {
        if (!$this->getProductIds()) {
            throw new EmptyWishlistException('You cannot delete products, the wishlist is empty.');
        }

        $this->session->remove(self::SESSION_ATTRIBUTE_NAME);
    }

    /**
     * @param int[] $wishlistProductIds
     * @param int $productId
     * @return bool
     */
    private function productAlreadyInWishList(array $wishlistProductIds, int $productId): bool
    {
        return in_array($productId, $wishlistProductIds);
    }

    /**
     * @param int[] $array
     * @param int $value
     */
    private function removeProductIdFromArray(array& $array, int $value): void
    {
        unset($array[array_search($value, $array)]);
    }

    /**
     * @param int[] $wishlistProductIds
     * @return bool
     */
    private function productLimitExceeded(array $wishlistProductIds): bool
    {
        return count($wishlistProductIds) >= self::LIMIT_OF_PRODUCTS;
    }
}
