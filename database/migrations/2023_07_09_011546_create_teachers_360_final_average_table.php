<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachers360FinalAverageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers_360_final_average', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->references('id')->on('users');
            $table->double('first_final_aggregate_competence_average')->nullable();
            $table->double('second_final_aggregate_competence_average')->nullable();
            $table->double('third_final_aggregate_competence_average')->nullable();
            $table->double('fourth_final_aggregate_competence_average')->nullable();
            $table->double('fifth_final_aggregate_competence_average')->nullable();
            $table->double('sixth_final_aggregate_competence_average')->nullable();
            $table->foreignId('assessment_period_id')->references('id')->on('assessment_periods');
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
        Schema::dropIfExists('teachers_360_final_average');
    }
}
