<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Exception\DuplicatedIdsException;
use App\Exception\InvalidFormatException;
use App\Exception\NotAllProductsWereFoundException;
use App\Repository\ProductRepositoryInterface;

class ExportProducts
{
    private const CSV_FORMAT = 'csv';
    private const AVAILABLE_FORMATS = [ self::CSV_FORMAT ];

    private ExportProductsSerializer $exportProductsSerializer;
    private ExportDataIntoFile $exportDataIntoFile;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        ExportProductsSerializer $exportProductsSerializer,
        ExportDataIntoFile $exportDataIntoFile,
        ProductRepositoryInterface $productRepository
    ) {
        $this->exportProductsSerializer = $exportProductsSerializer;
        $this->exportDataIntoFile = $exportDataIntoFile;
        $this->productRepository = $productRepository;
    }

    /**
     * @param string $name
     * @param string $format
     * @param string[] $ids
     * @throws InvalidFormatException
     * @throws DuplicatedIdsException
     * @throws NotAllProductsWereFoundException
     */
    public function execute(string $name, string $format, array $ids): void
    {
        $this->checkFormat($format);

        $serializedProducts = $this->exportProductsSerializer->serialize(
            $this->getProducts(array_map('intval', $ids)), $format
        );

        $this->exportDataIntoFile->execute($this->createFilename($name, $format), $serializedProducts);
    }

    /**
     * @param string $format
     * @throws InvalidFormatException
     */
    private function checkFormat(string $format): void
    {
        if (!in_array($format, self::AVAILABLE_FORMATS)) {
            throw new InvalidFormatException($format);
        }
    }

    /**
     * @param int[] $productIds
     * @param Product[] $products
     * @return bool
     */
    private function allIdsAreInRepository(array $productIds, array $products): bool
    {
        return count($productIds) === count($products);
    }

    /**
     * @param int[] $productIds
     * @return bool
     */
    private function idsAreDuplicated(array $productIds): bool
    {
        return count($productIds) !== count(array_unique($productIds));
    }

    /**
     * @param int[] $ids
     * @return Product[]
     * @throws DuplicatedIdsException
     * @throws NotAllProductsWereFoundException
     */
    private function getProducts(array $ids): array
    {
        if (empty($ids)) {
            return $this->productRepository->findAll();
        }

        if ($this->idsAreDuplicated($ids)) {
            throw new DuplicatedIdsException();
        }

        $products = $this->productRepository->getProductsByIds($ids);

        if (!$this->allIdsAreInRepository($ids, $products)) {
            throw new NotAllProductsWereFoundException();
        }

        return $products;
    }

    /**
     * @param string $name
     * @param string $format
     * @return string
     */
    private function createFilename(string $name, string $format): string
    {
        return $name . "." . $format;
    }
}
