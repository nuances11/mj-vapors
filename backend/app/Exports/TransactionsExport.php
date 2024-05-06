<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Excel;

class TransactionsExport implements FromView, ShouldAutoSize
{
    use Exportable;

    private $columns;
    private $data;
    private $header;

    public function __construct(array $request)
    {
        $this->columns = $request['columns'];
        $this->data = $request['data'];
        $this->header = $request['header'];
    }

    public function view(): \Illuminate\Contracts\View\View
    {
        return view('exports.reports.transaction', [
            'data' => $this->data,
            'headers' => $this->header,
            'columns' => $this->columns,
        ]);
    }
}
