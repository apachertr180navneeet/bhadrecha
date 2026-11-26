<?php

namespace App\Imports;

use App\Models\FuelCompany;
use App\Models\FuelPump;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FuelDetailImport implements ToArray, WithHeadingRow
{
    public function array(array $rows): array
    {
        return $rows;
    }
}
