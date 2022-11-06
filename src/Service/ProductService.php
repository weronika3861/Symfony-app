<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Exception\InvalidCategoryException;
use App\Exception\MissingAttributeException;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\Exception\ORMException;
use InvalidArgumentException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductService
{
    private ProductCategoryRepository $productCategoryRepository;
    private ProductRepository $productRepository;
    private ValidatorInterface $validator;
    private SerializerInterface $serializer;

    public function __construct(
        ProductCategoryRepository $productCategoryRepository,
        ProductRepository $productRepository,
        ValidatorInterface $validator,
        SerializerInterface $serializerWithObjectNormalizer
    ) {
        $this->productCategoryRepository = $productCategoryRepository;
        $this->productRepository = $productRepository;
        $this->validator = $validator;
        $this->serializer = $serializerWithObjectNormalizer;
    }

    /**
     * @return Product[]
     */
    public function findAll(): array
    {
        return $this->productRepository->findAll();
    }

    /**
     * @param int $id
     * @return ?Product
     */
    public function find(int $id): ?Product
    {
        return $this->productRepository->find($id);
    }

    /**
     * @param Product $product
     * @throws ORMException
     */
    public function addProduct(Product $product): void
    {
        $this->productRepository->add($product);
    }

    /**
     * @throws ORMException
     */
    public function editProduct(): void
    {
        $this->productRepository->edit();
    }

    /**
     * @param Product $product
     * @throws ORMException
     */
    public function deleteProduct(Product $product): void
    {
        $this->productRepository->delete($product);
    }

    /**
     * @param array $productData { name: string, description: string, categories: array{id: int} }
     * @throws InvalidCategoryException
     * @throws MissingAttributeException
     * @throws ORMException
     * @throws ExceptionInterface
     */
    public function add(array $productData): void
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
        $this->validate($product);

        $this->productRepository->add($product);
    }

    /**
     * @param Product $product
     * @param $productData array{ name: string, description: string, categories: array{id: int} }
     * @throws InvalidCategoryException
     * @throws ORMException
     * @throws ExceptionInterface
     */
    public function edit(Product $product, array $productData): void
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

            $this->editCategories($product, $categoriesIds);
        }

        $this->validate($product);

        $this->productRepository->edit($product);
    }

    /**
     * @param Product $product
     * @throws ORMException
     */
    public function delete(Product $product): void
    {
        $this->productRepository->delete($product);
    }

    /**
     * @param $categoriesFromProductData array{id: int}
     * @return int[]
     */
    private function getCategoriesIdsFromProductData(array $categoriesFromProductData): array
    {
        $categoriesIds = [];
        foreach ($categoriesFromProductData as $category) {
            $categoriesIds[] = $category['id'];
        }

        return $categoriesIds;
    }

    /**
     * @param int[] $categoryIds
     * @return ProductCategory[]
     */
    private function getCategoriesFromRepository(array $categoryIds): array
    {
        return $this->productCategoryRepository->findBy(['id' => $categoryIds]);
    }

    /**
     * @param int[] $categoriesIdsFromProductData
     * @param ProductCategory[] $categoriesFromRepository
     * @return bool
     */
    private function allCategoriesFromProductDataAreValid(
        array $categoriesIdsFromProductData,
        array $categoriesFromRepository
    ): bool {
        return count($categoriesIdsFromProductData) === count($categoriesFromRepository);
    }

    /**
     * @param $categoriesFromProductData array{id: int}
     * @return ProductCategory[]
     * @throws InvalidCategoryException
     */
    private function getValidCategories(array $categoriesFromProductData): array
    {
        $categoriesIds = $this->getCategoriesIdsFromProductData(
            $categoriesFromProductData
        );

        $categoriesFromRepository = $this->getCategoriesFromRepository($categoriesIds);

        if (!$this->allCategoriesFromProductDataAreValid($categoriesIds, $categoriesFromRepository)) {
            throw new InvalidCategoryException();
        }

        return $categoriesFromRepository;
    }

    /**
     * @param int[] $categoriesIds
     * @throws InvalidCategoryException
     */
    private function checkIfIsAnyInvalidCategory(array $categoriesIds): void
    {
        $categoriesFromRepository = $this->getCategoriesFromRepository($categoriesIds);

        if (!$this->allCategoriesFromProductDataAreValid($categoriesIds, $categoriesFromRepository)) {
            throw new InvalidCategoryException();
        }
    }

    /**
     * @param Product $product
     * @param int[] $categoriesIds
     * @throws InvalidCategoryException
     */
    private function editCategories(Product $product, array $categoriesIds): void
    {
        $this->checkIfIsAnyInvalidCategory($categoriesIds);

        $oldCategoriesIds = $this->getOldCategoriesIds($product);

        $categoriesToDeleteIds = array_diff($oldCategoriesIds, $categoriesIds);
        $categoriesToAddIds = array_diff($categoriesIds, $oldCategoriesIds);

        $this->deleteOldCategories($product, $categoriesToDeleteIds);
        $this->addNewCategories($product, $categoriesToAddIds);
    }

    /**
     * @param Product $product
     * @return int[]
     */
    private function getOldCategoriesIds(Product $product): array
    {
        $categoryIds = [];
        foreach ($product->getCategories() as $oldCategory) {
            $categoryIds[] = $oldCategory->getId();
        }

        return $categoryIds;
    }

    /**
     * @param Product $product
     * @param int[] $categoriesToDeleteIds
     */
    private function deleteOldCategories(Product $product, array $categoriesToDeleteIds): void
    {
        $categoriesToDelete = $this->productCategoryRepository->findBy(['id' => $categoriesToDeleteIds]);

        foreach ($categoriesToDelete as $category) {
            $product->removeCategory($category);
        }
    }

    /**
    * @param Product $product
    * @param int[] $categoriesToAddIds
    */
    private function addNewCategories(Product $product, array $categoriesToAddIds): void
    {
        $categoriesToAdd = $this->productCategoryRepository->findBy(['id' => $categoriesToAddIds]);

        foreach ($categoriesToAdd as $category) {
            $product->addCategory($category);
        }
    }

    /**
     * @param Product $product
     * @throws InvalidArgumentException
     */
    private function validate(Product $product): void
    {
        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            $invalidFields = [];

            /** @var ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $invalidFields[] = $error->getPropertyPath();
            }

            throw new InvalidArgumentException(implode(', ', $invalidFields));
        }
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
