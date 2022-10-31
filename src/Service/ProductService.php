<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;

class ProductService
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @return Product[]
     */
    public function findAll(): array
    {
        return $this->productRepository->findAll();
    }

    /**
     * @param Product $product
     */
    public function add(Product $product): void
    {
        $this->productRepository->add($product);
    }

    public function edit(): void
    {
        $this->productRepository->edit();
    }

    /**
     * @param Product $product
     */
    public function delete(Product $product): void
    {
        $this->productRepository->delete($product);
    }
}
