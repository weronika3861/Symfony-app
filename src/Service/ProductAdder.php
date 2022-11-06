<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\Exception\ORMException;

class ProductAdder
{
    private ProductRepository $productRepository;
    private ProductValidator $validatorService;

    public function __construct(ProductRepository $productRepository, ProductValidator $validatorService)
    {
        $this->productRepository = $productRepository;
        $this->validatorService = $validatorService;
    }

    /**
     * @throws ORMException
     */
    public function execute(Product $product): void
    {
        $this->validatorService->validate($product);
        $this->productRepository->add($product);
    }
}
