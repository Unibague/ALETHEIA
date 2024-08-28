<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;

class AllPeriodsReportPerGroupViewExport implements FromView
{

    private $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function view(): \Illuminate\Contracts\View\View
    {
        return view ('allPeriodsReportPerGroup', ['tableData' => $this->rows]);
    }
}
