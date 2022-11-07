<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\ProductImage;
use App\Entity\Product;
use App\Repository\ProductImageRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductImagesService
{
    private ProductImageRepository $imageRepository;
    private string $productImageDirectory;

    public function __construct(ProductImageRepository $imageRepository, string $productImageDirectory)
    {
        $this->imageRepository = $imageRepository;
        $this->productImageDirectory = $productImageDirectory;
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
        if (!$images) {
            return;
        }

        foreach ($images as $image) {
            $date = \DateTime::createFromFormat('0.u00 U', microtime());
            $date->setTimeZone(new \DateTimeZone('Europe/Warsaw'));

            $newFilename = $date->format('Y-m-d_H-i-s-u_') . $image->getClientOriginalName();
            $image->move($this->productImageDirectory, $newFilename);

            $imageObject = new ProductImage();
            $imageObject->setFilename($newFilename);
            $imageObject->setProduct($product);

            $this->imageRepository->add($imageObject);
        }
    }

    /**
     * @param Product $product
     * @return ProductImage[]
     */
    public function getImages(Product $product): array
    {
        return $product->getImages()->getValues();
    }
}
