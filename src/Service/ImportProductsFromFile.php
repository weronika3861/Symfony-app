<?php
declare(strict_types=1);

namespace App\Service;

use App\Exception\InvalidCategoryException;
use App\Exception\InvalidFormatException;
use App\Exception\MissingAttributeException;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ImportProductsFromFile
{
    public const CSV_FORMAT = 'csv';
    private const AVAILABLE_FORMATS = [ self::CSV_FORMAT ];

    private ImportDataFromCsv $importDataFromCsv;
    private ImportProducts $importProducts;
    private Filesystem $filesystem;

    public function __construct(
        ImportDataFromCsv $importDataFromCsv,
        ImportProducts $importProducts,
        FileSystem $filesystem
    ) {
        $this->importDataFromCsv = $importDataFromCsv;
        $this->importProducts = $importProducts;
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $name
     * @param string $format
     * @throws InvalidCategoryException
     * @throws ExceptionInterface
     * @throws MissingAttributeException
     * @throws ORMException
     * @throws InvalidFormatException
     */
    public function execute(string $name, string $format): void
    {
        $this->checkFormat($format);
        $filename = $this->createFilename($name, $format);
        $this->checkIfFileExist($filename);

        if ($format === self::CSV_FORMAT) {
            $data = $this->importDataFromCsv->execute($filename);
            $this->importProducts->execute($this->reformatData($data));
        }
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
     * @param string $filename
     * @throws FileNotFoundException
     */
    private function checkIfFileExist(string $filename): void
    {
        if (!$this->filesystem->exists($filename)) {
            throw new FileNotFoundException();
        }
    }

    /**
     * @param string $name
     * @param string $format
     * @return string
     */
    private function createFilename(string $name, string $format): string
    {
        return $name . '.' . $format;
    }

    /**
     * @param array $data { array{ name: string, categories.0.id: string, ... } }
     * @return array{ array{ name: string, description: string, categories: array{id: int} } }
     */
    private function reformatData(array $data): array
    {
        $newData = [];
        foreach ($data as $row) {
            $categoriesIds = $this->findCategoriesIds($row);

            $categories = [];
            foreach ($categoriesIds as $id) {
                $category['id'] = $id;
                array_push($categories, $category);
            }
            $row['categories'] = $categories;

            $newData[] = $row;
        }

        return $newData;
    }

    /**
     * @param array $row { name: string, categories.0.id: string, ... }
     * @return int[]
     */
    private function findCategoriesIds(array& $row): array
    {
        $categoriesIds = [];
        foreach ($row as $key => $value) {
            if (preg_match('/^categories\.\d\.id$/', $key)) {
                $categoriesIds[] = $value;
                unset($row[$key]);
            }
        }

        return array_map('intval', $categoriesIds);
    }
}
