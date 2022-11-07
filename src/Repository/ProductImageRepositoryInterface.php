<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductImage;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

interface ProductImageRepositoryInterface
{
    /**
     * @param ProductImage $image
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(ProductImage $image): void;

    /**
     * @param ProductImage[] $images
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteImages(array $images): void;
}
