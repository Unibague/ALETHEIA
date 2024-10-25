<?php

namespace App\Console\Commands;

use App\Models\AcademicPeriod;
use App\Models\AssessmentPeriod;
use App\Models\Reports;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class UpdateGroupResultsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:group-results-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert the student assessments for each teacher on each group on group_results table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        Reports::updateGroupResults();

        return 0;
    }
}
