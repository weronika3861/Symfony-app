<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Repository\ProductImageRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class ProductImageRemover
{
    private ProductImageRepository $productImageRepository;
    private Filesystem $filesystem;
    private string $productImageDirectory;

    public function __construct(
        ProductImageRepository $productImageRepository,
        Filesystem $filesystem,
        string $productImageDirectory
    ) {
        $this->productImageRepository = $productImageRepository;
        $this->filesystem = $filesystem;
        $this->productImageDirectory = $productImageDirectory;
    }

    /**
     * @param Product $product
     * @param ProductImage[] $images
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws IOException
     * @throws FileNotFoundException
     */
    public function execute(Product $product, array $images): void
    {
        foreach ($images as $image) {
            $file = $this->productImageDirectory . '/' . $image->getFilename();
            if (!$this->filesystem->exists($file)) {
                throw new FileNotFoundException();
            }

            $this->filesystem->remove($file);

            if ($image === $product->getMainImage()) {
                $product->setMainImage(null);
            }

            $product->removeImage($image);
        }

        $this->productImageRepository->deleteImages($images);
    }
}
