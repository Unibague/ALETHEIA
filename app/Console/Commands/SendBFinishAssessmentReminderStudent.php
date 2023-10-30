<?php

namespace App\Console\Commands;

use App\Models\AcademicPeriod;
use App\Models\AssessmentPeriod;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendBFinishAssessmentReminderStudent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder_student:send_b_finish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $emailsToSent = DB::table('assessment_reminder_users')->where('status', '=', 'Not Started')
            ->where('before_start_or_finish_assessment', '=', 'Finish')
            ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->take(100)->get();
        $reference = $emailsToSent;

        if (count($emailsToSent) == 0) {

            $emailsToSent = DB::table('assessment_reminder_users')->where('status', '=', 'In Progress')
                ->where('before_start_or_finish_assessment', '=', 'Finish')
                ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->take(100)->get();
        }
//    dd($emailsToSent);

        foreach ($emailsToSent as $student){

            $emailParameters = json_decode($student->email_parameters);

            DB::table('assessment_reminder_users')->where('assessment_period_id', '=', $activeAssessmentPeriodId)
                ->where('id', '=', $student->id)->update(['status' => 'In Progress']);

            $data = [
                'role' => $emailParameters->role,
                'name' => $emailParameters->name,
                'teachers_to_evaluate' => $emailParameters->teachers_to_evaluate,
                'end_date' => $emailParameters->end_date,
                'assessment_period_name' => AssessmentPeriod::getActiveAssessmentPeriod()->name
            ];

            $email = new \App\Mail\SecondReminderMailable($data);
            Mail::bcc([$student->email])->send($email);
            DB::table('assessment_reminder_users')->where('assessment_period_id', '=', $activeAssessmentPeriodId)
                ->where('id', '=', $student->id)->update(['status' => 'Done']);
        }

        if (count($reference) > 0) {
            $issue = 'Lote de Estudiantes previos a empezar evaluación docente';
            $confirmationEmail = new \App\Mail\ConfirmationFinishSend($issue);
            Mail::bcc(['juanes01.gonzalez@gmail.com'])->send($confirmationEmail);
        }   

        return 0;
    }
}
