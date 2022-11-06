<?php
declare(strict_types=1);

namespace App\Service;

use App\Exception\InvalidCategoryException;
use App\Exception\MissingAttributeException;
use App\Repository\ProductRepository;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ImportProducts
{
    private ArrayProductDenormalizer $arrayProductDenormalizer;
    private ProductService $productService;
    private ProductRepository $productRepository;

    public function __construct(
        ArrayProductDenormalizer $arrayProductDenormalizer,
        ProductService $productService,
        ProductRepository $productRepository
    ) {
        $this->arrayProductDenormalizer = $arrayProductDenormalizer;
        $this->productService = $productService;
        $this->productRepository = $productRepository;
    }

    /**
     * @param array{ array{ name: string, description: string, categories: array{id: int} }
     * @throws ORMException
     * @throws InvalidCategoryException
     * @throws ExceptionInterface
     * @throws MissingAttributeException
     */
    public function execute(array $data): void
    {
        $importedProductsData = [];
        foreach ($data as $row) {
            $importedProductsData[$row['name']] = $row;
        }

        $productsFromDatabase = $this->productRepository->getProductsByNames(array_keys($importedProductsData));

        foreach ($productsFromDatabase as $product) {
            $name = $product->getName();

            $this->productService->edit($product, $importedProductsData[$name]);
            unset($importedProductsData[$name]);
        }

        foreach ($importedProductsData as $data) {
            $this->productService->add($this->arrayProductDenormalizer->execute($data));
        }
    }
}
