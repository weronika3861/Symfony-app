<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Exception\InvalidCategoryException;
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

    /**
     * @param Product& $product
     * @param int[] $categoriesIds
     * @throws InvalidCategoryException
     */
    public function editCategories(Product& $product, array $categoriesIds): void
    {
        $this->checkInvalidCategoriesId($categoriesIds);

        $oldCategoriesIds = $this->getProductCategoriesIds($product);

        $categoriesToDeleteIds = array_diff($oldCategoriesIds, $categoriesIds);
        $categoriesToAddIds = array_diff($categoriesIds, $oldCategoriesIds);

        $this->deleteCategories($product, $categoriesToDeleteIds);
        $this->addCategories($product, $categoriesToAddIds);
    }

    /**
     * @param Product $product
     * @param int[] $categoriesToAddIds
     */
    private function addCategories(Product $product, array $categoriesToAddIds): void
    {
        $categoriesToAdd = $this->productCategoryRepository->findBy(['id' => $categoriesToAddIds]);

        foreach ($categoriesToAdd as $category) {
            $product->addCategory($category);
        }
    }

    /**
     * @param Product $product
     * @param int[] $categoriesToDeleteIds
     */
    private function deleteCategories(Product $product, array $categoriesToDeleteIds): void
    {
        $categoriesToDelete = $this->productCategoryRepository->findBy(['id' => $categoriesToDeleteIds]);

        foreach ($categoriesToDelete as $category) {
            $product->removeCategory($category);
        }
    }

    /**
     * @param int[] $categoriesIds
     * @throws InvalidCategoryException
     */
    private function checkInvalidCategoriesId(array $categoriesIds): void
    {
        $categoriesFromRepository = $this->productCategoryRepository->findByIds($categoriesIds);

        if (!$this->allCategoriesIdsAreValid($categoriesIds, $categoriesFromRepository)) {
            throw new InvalidCategoryException();
        }
    }

    /**
     * @param Product $product
     * @return int[]
     */
    private function getProductCategoriesIds(Product $product): array
    {
        $categoriesIds = [];
        foreach ($product->getCategories() as $category) {
            $categoriesIds[] = $category->getId();
        }

        return $categoriesIds;
    }

    /**
     * @param int[] $categoriesIds
     * @param ProductCategory[] $categoriesFromRepository
     * @return bool
     */
    public function allCategoriesIdsAreValid(
        array $categoriesIds,
        array $categoriesFromRepository
    ): bool {
        return count($categoriesIds) === count($categoriesFromRepository);
    }
}
