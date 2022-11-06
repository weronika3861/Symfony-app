<?php
declare(strict_types=1);

namespace App\Command;

use App\Exception\DuplicatedIdsException;
use App\Exception\InvalidFormatException;
use App\Exception\NotAllProductsWereFoundException;
use App\Service\ExportProducts;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportProductsCommand extends Command
{
    protected static $defaultName = 'app:export-products';

    private ExportProducts $exportProductsService;

    public function __construct(ExportProducts $exportProductsService)
    {
        $this->exportProductsService = $exportProductsService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Name of file.');
        $this->addArgument('format', InputArgument::REQUIRED, 'File format.');
        $this->addArgument(
            'ids',
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Product id list (separate multiple ids with a space).'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->exportProductsService->execute(
                $input->getArgument('name'),
                $input->getArgument('format'),
                $input->getArgument('ids')
            );
        } catch (InvalidFormatException $e) {
            $output->writeln('Invalid format: ' . $e->getMessage());

            return Command::INVALID;
        } catch (NotAllProductsWereFoundException $e) {
            $output->writeln('Not all products were found.');

            return Command::INVALID;
        } catch (DuplicatedIdsException $e) {
            $output->writeln('You entered duplicated ids.');

            return Command::INVALID;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
