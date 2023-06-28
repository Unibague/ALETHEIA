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
            $table->timestamps();
            $table->foreignId('teacher_id')->references('id')->on('users');
            $table->foreignId('group_id')->references('group_id')->on('groups');
            $table->double('first_final_competence_average')->nullable();
            $table->double('second_final_competence_average')->nullable();
            $table->double('third_final_competence_average')->nullable();
            $table->double('fourth_final_competence_average')->nullable();
            $table->double('fifth_final_competence_average')->nullable();
            $table->double('sixth_final_competence_average')->nullable();
            $table->integer('students_amount');
            $table->foreignId('assessment_period_id')->references('id')->on('assessment_periods');

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
