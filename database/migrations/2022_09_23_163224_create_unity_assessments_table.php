<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnityAssessmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unity_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluated_id');
            $table->foreign('evaluated_id')->references('id')->on('users');
            $table->foreignId('evaluator_id');
            $table->foreign('evaluator_id')->references('id')->on('users');
            $table->enum('role',['jefe','par','autoevaluación']);
            $table->boolean('pending');
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
        Schema::dropIfExists('unity_assessments');
    }
}
