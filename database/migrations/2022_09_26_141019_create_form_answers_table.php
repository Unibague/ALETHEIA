<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('form_id')->constrained();
            $table->json('answers');
            $table->dateTime('submitted_at');
            $table->unsignedBigInteger('group_id');
            $table->foreign('group_id')->references('group_id')->on('groups');
            $table->foreignId('teacher_id')->nullable()->references('id')->on('users');
            $table->foreignId('unity_assessment_id')->nullable()->constrained();
            $table->double('first_competence_average')->nullable();
            $table->double('second_competence_average')->nullable();
            $table->double('third_competence_average')->nullable();
            $table->double('fourth_competence_average')->nullable();
            $table->double('fifth_competence_average')->nullable();
            $table->double('sixth_competence_average')->nullable();
            $table->foreignId('assessment_period_id')->nullable()->constrained();
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
        Schema::dropIfExists('form_answers');
    }
}
