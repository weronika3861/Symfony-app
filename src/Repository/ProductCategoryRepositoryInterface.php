<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductCategory;

interface ProductCategoryRepositoryInterface
{
    /**
     * @param int[] $ids
     * @return ProductCategory[]
     */
    public function findByIds(array $ids): array;
}
