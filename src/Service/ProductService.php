<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Exception\InvalidCategoryException;
use App\Exception\MissingAttributeException;
use App\Exception\ProductNotExistException;
use App\Repository\ProductRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductService
{
    private ProductImagesService $productImagesService;
    private ProductAdder $productAdder;
    private ProductEditor $productEditor;
    private ProductRemover $productRemover;
    private ProductRepository $productRepository;
    private ProductImageRemover $productImageRemover;

    public function __construct(
        ProductImagesService $productImagesService,
        ProductAdder $productAdder,
        ProductEditor $productEditor,
        ProductRemover $productRemover,
        ProductRepository $productRepository,
        ProductImageRemover $productImageRemover
    ) {
        $this->productImagesService = $productImagesService;
        $this->productAdder = $productAdder;
        $this->productEditor = $productEditor;
        $this->productRemover = $productRemover;
        $this->productRepository = $productRepository;
        $this->productImageRemover = $productImageRemover;
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
     * @param int $id
     * @return ?Product
     * @throws ProductNotExistException
     */
    public function get(int $id): Product
    {
        return $this->productRepository->get($id);
    }

    /**
     * @param Product $product
     * @throws ORMException
     */
    public function add(Product $product): void
    {
        $this->productAdder->execute($product);
    }

    /**
     * @param Product $product
     * @param array $productData { name: string, description: string, categories: array{id: int} }
     * @throws InvalidCategoryException
     * @throws MissingAttributeException
     * @throws ORMException
     */
    public function edit(Product $product, array $productData): void
    {
        $this->productEditor->execute($product, $productData);
    }

    /**
     * @param Product $product
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws IOException
     * @throws FileNotFoundException
     */
    public function delete(Product $product): void
    {
        $this->productRemover->execute($product);
    }

    /**
     * @param Product $product
     * @return ProductImage[]
     */
    public function getImages(Product $product): array
    {
        return $this->productImagesService->getImages($product);
    }

    /**
     * @param Product $product
     * @param UploadedFile[] $images
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws FileException
     */
    public function attachImages(Product $product, array $images): void
    {
        $this->productImagesService->attachImages($product, $images);
    }

    /**
     * @param Product $product
     * @param ProductImage[] $images
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws FileNotFoundException
     */
    public function removeImages(Product $product, array $images): void
    {
        $this->productImageRemover->execute($product, $images);
    }
}
