<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AdBlueLedgerExport implements FromView, ShouldAutoSize
{
    protected $ledgerItems;

    public function __construct($ledgerItems)
    {
        $this->ledgerItems = $ledgerItems;
    }

    public function view(): View
    {
        return view('admin.transport.trips.exports.adblue-ledger', [
            'ledgerItems' => $this->ledgerItems
        ]);
    }
}
