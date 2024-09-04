<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\FormAnswers;

class ProcessFormAnswersBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $batch;
    protected $batchSize;

    public function __construct($batch, $batchSize)
    {
        $this->batch = $batch;
        $this->batchSize = $batchSize;
    }

    public function handle()
    {
        DB::table('form_answers')
            ->whereNull('competences_average')
            ->offset($this->batch * $this->batchSize)
            ->limit($this->batchSize)
            ->orderBy('id')
            ->chunk(100, function ($formAnswers) {
                foreach ($formAnswers as $formAnswer) {
                    $answers = json_decode($formAnswer->answers, JSON_THROW_ON_ERROR);
                    $results = FormAnswers::getCompetencesAverage($answers);
                    $openEndedAnswers = FormAnswers::getOpenEndedAnswersFromFormAnswer($answers);

                    DB::table('form_answers')->updateOrInsert(
                        ['id' => $formAnswer->id],
                        [
                            'competences_average' => $results['competences'],
                            'overall_average' => $results['overall_average'],
                            'open_ended_answers' => json_encode($openEndedAnswers)
                        ]
                    );
                }
            });
    }
}
