<?php
declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;

class ExportDataIntoFile
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $filename
     * @param string $data
     */
    public function execute(string $filename, string $data): void
    {
        $this->filesystem->dumpFile($filename, $data);
    }
}
