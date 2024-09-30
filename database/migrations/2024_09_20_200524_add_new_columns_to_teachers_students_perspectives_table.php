<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToTeachersStudentsPerspectivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teachers_students_perspectives', function (Blueprint $table) {
            $table->json('competences_average')->nullable();
            $table->double('overall_average')->nullable();
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
            $table->dropColumn('competences_average');
            $table->dropColumn('overall_average');
        });
    }
}
