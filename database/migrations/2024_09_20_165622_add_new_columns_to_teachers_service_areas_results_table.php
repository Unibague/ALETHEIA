<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToTeachersServiceAreasResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teachers_service_areas_results', function (Blueprint $table) {
            $table->json('competences_average')->nullable();
            $table->double('overall_average')->nullable();
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
        Schema::table('teachers_service_areas_results', function (Blueprint $table) {
            $table->dropColumn('competences_average');
            $table->dropColumn('overall_average');
            $table->dropColumn('open_ended_answers');
        });
    }
}