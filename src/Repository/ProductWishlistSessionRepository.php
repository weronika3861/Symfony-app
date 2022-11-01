<?php
declare(strict_types=1);

namespace App\Repository;

use App\Exception\ProductAlreadyInWishlistException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ProductWishlistSessionRepository implements ProductWishlistRepositoryInterface
{
    private const SESSION_ATTRIBUTE_NAME = 'wishlistProductIds';

    private Session $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param int $productId
     * @throws ProductAlreadyInWishlistException
     */
    public function addByProductId(int $productId): void
    {
        if (!$wishlistProductIds = $this->getProductIds()) {
            $wishlistProductIds = [];
        }

        if ($this->productAlreadyInWishList($productId, $wishlistProductIds)) {
            throw new ProductAlreadyInWishlistException('Product is already in the wishlist.');
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
     * @param int[] $wishlistProductIds
     * @return bool
     */
    private function productAlreadyInWishList(int $productId, array $wishlistProductIds): bool
    {
        return in_array($productId, $wishlistProductIds);
    }
}
