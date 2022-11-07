<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Exception\InvalidCategoryException;
use App\Exception\MissingAttributeException;
use App\Repository\ProductCategoryRepository;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ArrayProductDenormalizer
{
    private const REQUIRED_FIELDS = ['name', 'description', 'categories'];

    private SerializerInterface $serializer;
    private ProductCategoryService $productCategoryService;
    private ProductCategoryRepository $productCategoryRepository;

    public function __construct(
        SerializerInterface $serializer,
        ProductCategoryService $productCategoryService,
        ProductCategoryRepository $productCategoryRepository
    ) {
        $this->serializer = $serializer;
        $this->productCategoryService = $productCategoryService;
        $this->productCategoryRepository = $productCategoryRepository;
    }

    /**
     * @param ?Product $product
     * @param array $productData { name: string, description: string, categories: array{id: int} }
     * @return Product
     * @throws InvalidCategoryException
     * @throws MissingAttributeException
     * @throws ExceptionInterface
     */
    public function execute(array $productData, ?Product $product): Product
    {
        if (is_null($product)) {
            return $this->denormalizeNewProduct($productData);
        }

        return $this->denormalizeExistingProduct($product, $productData);
    }

    /**
     * @param array $productData { name: string, description: string, categories: array{id: int} }
     * @return Product
     * @throws ExceptionInterface
     * @throws MissingAttributeException
     * @throws InvalidCategoryException
     */
    private function denormalizeNewProduct(array $productData): Product
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
     * @param Product $product
     * @param array $productData { name: string, description: string, categories: array{id: int} }
     * @return Product
     * @throws InvalidCategoryException
     * @throws ExceptionInterface
     */
    private function denormalizeExistingProduct(Product $product, array $productData): Product
    {
        $categoriesData = $productData['categories'];
        unset($productData['categories']);

        $this->serializer->denormalize(
            $productData,
            Product::class,
            null,
            [AbstractNormalizer::OBJECT_TO_POPULATE => $product]
        );

        if (isset($categoriesData)) {
            $categoriesIds = $this->getCategoriesIdsFromProductData($categoriesData);

            $this->productCategoryService->editCategories($product, $categoriesIds);
        }

        return $product;
    }

    /**
     * @param array $productData { name: string, description: string, categories: array{id: int} }
     * @return bool
     */
    private function allAttributesAreSet(array $productData): bool
    {
        foreach (self::REQUIRED_FIELDS as $required_field) {
            if (!isset($productData[$required_field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $categoriesFromProductData {id: int}
     * @return ProductCategory[]
     * @throws InvalidCategoryException
     */
    private function getValidCategories(array $categoriesFromProductData): array
    {
        $categoriesIds = $this->getCategoriesIdsFromProductData($categoriesFromProductData);

        $categoriesFromRepository = $this->productCategoryRepository->findByIds($categoriesIds);

        if (!$this->allCategoriesFromProductDataAreValid($categoriesIds, $categoriesFromRepository)) {
            throw new InvalidCategoryException();
        }

        return $categoriesFromRepository;
    }

    /**
     * @param int[] $categoriesIdsFromProductData
     * @param ProductCategory[] $categoriesFromRepository
     * @return bool
     */
    private function allCategoriesFromProductDataAreValid(
        array $categoriesIdsFromProductData,
        array $categoriesFromRepository
    ): bool
    {
        return count($categoriesIdsFromProductData) === count($categoriesFromRepository);
    }

    /**
     * @param array $categories {id: int}
     * @return int[]
     */
    private function getCategoriesIdsFromProductData(array $categories): array
    {
        $categoriesIds = [];
        foreach ($categories as $category) {
            $categoriesIds = $category['id'];
        }

        return $categoriesIds;
    }
}
