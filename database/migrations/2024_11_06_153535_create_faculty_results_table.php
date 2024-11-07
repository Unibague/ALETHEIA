<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacultyResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faculty_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained();
            $table->enum('hour_type', ['normal', 'cátedra', 'total'])->nullable();
            $table->integer('students_enrolled');
            $table->integer('students_reviewers');
            $table->foreignId('assessment_period_id')->constrained();
            $table->json('competences_average')->nullable();
            $table->double('overall_average')->nullable();
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
        Schema::dropIfExists('faculty_results');
    }
}
