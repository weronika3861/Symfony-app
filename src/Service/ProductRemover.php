<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;

class ProductRemover
{
    private ProductRepository $productRepository;
    private ProductImageRemover $imageRemover;

    public function __construct(ProductRepository $productRepository, ProductImageRemover $imageRemover)
    {
        $this->productRepository = $productRepository;
        $this->imageRemover = $imageRemover;
    }

    /**
     * @param Product $product
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws IOException
     * @throws FileNotFoundException
     */
    public function execute(Product $product): void
    {
        $this->imageRemover->execute($product, $product->getImages()->getValues());
        $this->productRepository->delete($product);
    }
}
