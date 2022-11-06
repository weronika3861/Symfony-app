<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use Doctrine\ORM\Exception\ORMException;

interface ProductRepositoryInterface
{
    /**
     * @param Product $product
     * @throws ORMException
     */
    public function add(Product $product): void;

    /**
     * @param Product $product
     * @throws ORMException
     */
    public function delete(Product $product): void;

    /**
     * @throws ORMException
     */
    public function edit(): void;

    /**
     * @param int[] $productsId
     * @return Product[]
     */
    public function getProductsByIds(array $productsId): array;
}
