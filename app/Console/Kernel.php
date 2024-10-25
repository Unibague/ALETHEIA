<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // First command
        $schedule->command('reports:group-results-update')->cron('4 * * * *');

        $schedule->command('reports:teacher-service-area-results-update')->cron('6 * * * *');

        $schedule->command('reports:teacher-student-perspective-results-update')->cron('14 * * * *');


//        //Check if today is the day to send a reminder that assessment period starts
//        $schedule->command('academic_periods:check_due_b_start')->daily()->at('06:00');
//        //Check if today is the day to send a reminder that assessment period finishes
//        $schedule->command('academic_periods:check_due_b_finish')->daily()->at('07:00');
//        //Send the emails before start assessment to the students that are in the table with the Not Started status (assessment_reminder_users)
//        $schedule->command('reminder_student:send_b_start')->everyFiveMinutes();
//        //Send the emails before finish assessment to the students that are in the table with the Not Started status (assessment_reminder_users)
//        $schedule->command('reminder_student:send_b_finish')->everyFiveMinutes();
//
//        //Send the emails to the teachers when it's the due date (this cronjob will only execute once a day
//        $schedule->command('reminder_teachers:send')->daily()->at('14:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
