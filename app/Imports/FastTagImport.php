<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FastTagImport implements ToArray, WithHeadingRow
{
    public function array(array $rows): array
    {
        return $rows;
    }
}
