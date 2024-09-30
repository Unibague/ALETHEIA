<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHourTypeToTeachersStudentsPerspectivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teachers_students_perspectives', function (Blueprint $table) {
            $table->enum('hour_type', ['normal', 'cátedra', 'total'])->nullable();
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
            $table->dropColumn('hour_type');
        });
    }
}
