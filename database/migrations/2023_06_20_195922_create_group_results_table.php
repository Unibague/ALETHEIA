<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->references('id')->on('users');
            $table->foreignId('group_id')->references('group_id')->on('groups');
            $table->string('service_area_code');
            $table->enum('hour_type', ['normal', 'cátedra']);
            $table->foreign('service_area_code')->references('code')->on('service_areas');
            $table->double('first_final_competence_average')->nullable();
            $table->double('second_final_competence_average')->nullable();
            $table->double('third_final_competence_average')->nullable();
            $table->double('fourth_final_competence_average')->nullable();
            $table->double('fifth_final_competence_average')->nullable();
            $table->double('sixth_final_competence_average')->nullable();
            $table->integer('students_amount_reviewers');
            $table->integer('students_amount_on_group');
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
        Schema::dropIfExists('group_results');
    }
}
