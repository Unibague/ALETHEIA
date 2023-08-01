<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentReminderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessment_reminder', function (Blueprint $table) {
            $table->id();
            $table->string('reminder_name');
            $table->enum('roles', ['Todos','Jefe', 'Par', 'Autoevaluación', 'Estudiante']);
            $table->enum('send_reminder_before', ['start', 'finish']);
            $table->integer('days_in_advance');
            $table->foreignId('assessment_period_id')->references('id')->on('assessment_periods');
            $table->timestamps();


            /*$table->foreignId('user_id')->constrained();
            $table->enum('status', ['Not Started', 'In Progress', 'Done']);
            $table->foreignId('assessment_period_id')->references('id')->on('assessment_periods');*/

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assessment_reminder');
    }
}
