<?php
declare(strict_types=1);

namespace App\Command;

use App\Exception\InvalidCategoryException;
use App\Exception\InvalidFormatException;
use App\Exception\MissingAttributeException;
use App\Service\ImportProductsFromFile;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ImportProductsFromCsvCommand extends Command
{
    protected static $defaultName = 'app:import-products-from-csv';

    private ImportProductsFromFile $importProductsFromFileService;

    public function __construct(ImportProductsFromFile $importProductsFromFileService)
    {
        $this->importProductsFromFileService = $importProductsFromFileService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('This command allows you to import products from csv file.');
        $this->setHelp(
            'The csv file should contain a header column with arguments.
            Name of product and categories ids are necessary. Other product attributes are optional.
            Header example: name,categories.0.id,categories.1.id'
        );
        $this->addArgument('name', InputArgument::REQUIRED, 'Name of file.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->importProductsFromFileService->execute(
                $input->getArgument('name'),
                $this->importProductsFromFileService::CSV_FORMAT
            );
        } catch (InvalidFormatException $e) {
            $output->writeln('Invalid format: ' . $e->getMessage());

            return Command::INVALID;
        } catch (FileNotFoundException $e) {
            $output->writeln('File not found.');

            return Command::INVALID;
        } catch (InvalidCategoryException $e) {
            $output->writeln('Assigned invalid category');

            return Command::INVALID;
        } catch (\InvalidArgumentException $e) {
            $output->writeln('Invalid arguments: ' . $e->getMessage());

            return Command::INVALID;
        } catch (MissingAttributeException $e) {
            $output->writeln('Missing attribute: ' . $e->getMessage());

            return Command::INVALID;
        } catch (ORMException $e) {
            $output->writeln('Import data to database was failed.');

            return Command::FAILURE;
        } catch (ExceptionInterface | \Exception $e) {
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
