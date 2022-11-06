<?php
declare(strict_types=1);

namespace App\Service;

class ImportDataFromCsv
{
    /**
     * @param string $filename
     * @return array{ array{ name: string, categories.0.id: string, ... } }
     */
    public function execute(string $filename): array
    {
        $csv = array_map('str_getcsv', file($filename,FILE_SKIP_EMPTY_LINES));
        $keys = array_shift($csv);

        foreach ($csv as $i => $row) {
            $csv[$i] = $this->combineArrayWithDifferentSize($keys, $row);
        }

        return $csv;
    }

    /**
     * @param string[] $a
     * @param string[] $b
     * @return array{ name: string, categories.0.id: string, ... }
     */
    private function combineArrayWithDifferentSize(array $a, array $b): array
    {
        $acount = count($a);
        $bcount = count($b);
        $size = ($acount > $bcount) ? $bcount : $acount;

        $a = array_slice($a, 0, $size);
        $b = array_slice($b, 0, $size);

        return array_combine($a, $b);
    }
}
