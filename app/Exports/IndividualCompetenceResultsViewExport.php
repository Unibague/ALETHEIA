<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;

class IndividualCompetenceResultsViewExport implements FromView
{
    private $questions;

    private $rows;


    public function __construct($questions, $rows)
    {
        $this->questions = $questions;
        $this->rows = $rows;
    }

    public function view(): \Illuminate\Contracts\View\View
    {
        return view ('singleCompetenceReport', ['questions' => $this->questions, 'tableData' => $this->rows]);
    }
}
