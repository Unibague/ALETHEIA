<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachersServiceAreasResults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers_service_areas_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->references('id')->on('users');
            $table->string('service_area_code');
            $table->foreign('service_area_code')->references('code')->on('service_areas');
            $table->double('first_final_aggregate_competence_average')->nullable();
            $table->double('second_final_aggregate_competence_average')->nullable();
            $table->double('third_final_aggregate_competence_average')->nullable();
            $table->double('fourth_final_aggregate_competence_average')->nullable();
            $table->double('fifth_final_aggregate_competence_average')->nullable();
            $table->double('sixth_final_aggregate_competence_average')->nullable();
            $table->integer('aggregate_students_amount_reviewers');
            $table->integer('aggregate_students_amount_on_service_area');
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
        Schema::dropIfExists('teachers_service_areas_results');
    }
}
