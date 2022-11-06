<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Exception\InvalidCategoryException;
use App\Exception\MissingAttributeException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ArrayProductDenormalizer
{
    private ProductService $productService;
    private SerializerInterface $serializer;

    public function __construct(ProductService $productService, SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->productService = $productService;
    }

    /**
     * @param array $productData { name: string, description: string, categories: array{id: int} }
     * @return Product
     * @throws ExceptionInterface
     * @throws MissingAttributeException
     * @throws InvalidCategoryException
     */
    public function execute(array $productData): Product
    {
        if (!$this->allAttributesAreSet($productData)) {
            throw new MissingAttributeException();
        }

        /** @var Product $product */
        $product = $this->serializer->denormalize($productData, Product::class);

        $categories = $this->getValidCategories($productData['categories']);
        $product->removeCategories();

        foreach ($categories as $category) {
            $product->addCategory($category);
        }

        return $product;
    }

    /**
     * @param $categoriesFromProductData array{id: int}
     * @return ProductCategory[]
     * @throws InvalidCategoryException
     */
    private function getValidCategories(array $categoriesFromProductData): array
    {
        $categoriesIds = $this->productService->getCategoriesIdsFromProductData(
            $categoriesFromProductData
        );

        $categoriesFromRepository = $this->productService->getCategoriesFromRepository($categoriesIds);

        if (!$this->productService->allCategoriesFromProductDataAreValid($categoriesIds, $categoriesFromRepository)) {
            throw new InvalidCategoryException();
        }

        return $categoriesFromRepository;
    }

    /**
     * @param array $productData { name: string, description: string, categories: array{id: int} }
     * @return bool
     */
    private function allAttributesAreSet(array $productData): bool
    {
        return isset(
            $productData['name'],
            $productData['description'],
            $productData['categories']
        );
    }
}
