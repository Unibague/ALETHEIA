<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpenEndedAnswersToTeachersStudentsPerspectivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teachers_students_perspectives', function (Blueprint $table) {
            $table->json('open_ended_answers')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teachers_students_perspectives', function (Blueprint $table) {
            $table->dropColumn('open_ended_answers');
        });
    }
}
