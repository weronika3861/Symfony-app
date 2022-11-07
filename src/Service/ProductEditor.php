<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Exception\InvalidCategoryException;
use App\Exception\MissingAttributeException;
use App\Repository\ProductRepository;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ProductEditor
{
    private ProductRepository $productRepository;
    private ProductValidator $validatorService;
    private ArrayProductDenormalizer $arrayProductDenormalizer;

    public function __construct(
        ProductRepository $productRepository,
        ProductValidator $validatorService,
        ArrayProductDenormalizer $arrayProductDenormalizer
    ) {
        $this->productRepository = $productRepository;
        $this->validatorService = $validatorService;
        $this->arrayProductDenormalizer = $arrayProductDenormalizer;
    }

    /**
     * @param Product $product
     * @param array $productData { name: string, description: string, categories: array{id: int} }
     * @throws ORMException
     * @throws InvalidCategoryException
     * @throws MissingAttributeException
     * @throws ExceptionInterface
     */
    public function execute(Product $product, array $productData): void
    {
        if (!empty($productDatap)) {
            $product = $this->arrayProductDenormalizer->execute($productData, $product);
        }

        $this->validatorService->validate($product);
        $this->productRepository->edit();
    }
}
