<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoledAndTotalActorsColumnsToTeachers360Final extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teachers_360_final_average', function (Blueprint $table) {
            $table->integer('involved_actors')->nullable();
            $table->integer('total_actors')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teachers_360_final_average', function (Blueprint $table) {
            $table->dropColumn('involved_actors');
            $table->dropColumn('total_actors');
        });
    }
}
