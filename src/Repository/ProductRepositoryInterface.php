<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use App\Exception\ProductNotExistException;
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
     * @param int[] $productsIds
     * @return Product[]
     */
    public function getProductsByIds(array $productsIds): array;

    /**
     * @param string[] $productsNames
     * @return Product[]
     */
    public function getProductsByNames(array $productsNames): array;

    /**
     * @param int $id
     * @return Product
     * @throws ProductNotExistException
     */
    public function get(int $id): Product;
}
