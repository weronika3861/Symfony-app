<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\ProductCategory;
use App\Repository\ProductCategoryRepository;

class ProductCategoryService
{
    private ProductCategoryRepository $productCategoryRepository;

    public function __construct(ProductCategoryRepository $productCategoryRepository)
    {
        $this->productCategoryRepository = $productCategoryRepository;
    }

    /**
     * @return ProductCategory[]
     */
    public function findAll(): array
    {
        return $this->productCategoryRepository->findAll();
    }

    /**
     * @param ProductCategory $productCategory
     */
    public function add(ProductCategory $productCategory): void
    {
        $this->productCategoryRepository->add($productCategory);
    }

    public function edit(): void
    {
        $this->productCategoryRepository->edit();
    }

    /**
     * @param ProductCategory $productCategory
     */
    public function delete(ProductCategory $productCategory): void
    {
        $this->productCategoryRepository->delete($productCategory);
    }
}
