<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceAreaResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_area_results', function (Blueprint $table) {
            $table->id();
            $table->string('service_area_code');
            $table->foreign('service_area_code')->references('code')->on('service_areas');
            $table->enum('hour_type', ['normal', 'cátedra', 'total'])->nullable();
            $table->integer('students_enrolled');
            $table->integer('students_reviewers');
            $table->foreignId('assessment_period_id')->constrained();
            $table->json('competences_average')->nullable();
            $table->json('open_ended_answers')->nullable();
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
        Schema::dropIfExists('service_area_results');
    }
}
