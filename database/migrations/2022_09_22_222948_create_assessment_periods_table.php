<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentPeriodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessment_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->dateTime('self_start_date');
            $table->dateTime('self_end_date');
            $table->dateTime('boss_start_date');
            $table->dateTime('boss_end_date');
            $table->dateTime('colleague_start_date');
            $table->dateTime('colleague_end_date');
            $table->boolean('done_by_none');
            $table->boolean('done_by_auxiliary');
            $table->boolean('done_by_assistant');
            $table->boolean('done_by_associated');
            $table->boolean('head_teacher');
            $table->boolean('active');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assessment_periods');
    }
}
