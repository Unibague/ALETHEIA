<?php

namespace App\Console\Commands;

use App\Models\AcademicPeriod;
use App\Models\AssessmentPeriod;
use App\Models\Reports;
use Carbon\Carbon;
use Facade\FlareClient\Report;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class UpdateReportTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:update-results';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Method updates all the tables required to generate the reports for assessment in the current assessment period active';

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
        $academicPeriods = AcademicPeriod::getCurrentAcademicPeriods();

        foreach ($academicPeriods as $academicPeriod) {
            if (!AcademicPeriod::isStudentsAssessmentDateFinished($academicPeriod)) {
                continue;
            }

            try {
                Reports::updateGroupResults($academicPeriod);
                Reports::updateTeacherServiceAreaResults();
                Reports::updateTeacherStudentPerspectiveResultsTable();
                Reports::updateServiceAreaResults();
                Reports::updateFacultyResults();
            } catch (\Exception $exception){
                \Log::error($exception->getMessage() . Carbon::now()->toDateTimeString());
            }

        }
        return 0;
    }
}
