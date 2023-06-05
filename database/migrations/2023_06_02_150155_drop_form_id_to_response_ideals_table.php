<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropFormIdToResponseIdealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('response_ideals', function (Blueprint $table) {

            $table->dropForeign(['form_id']);
            $table->dropColumn(['form_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('response_ideals', function (Blueprint $table) {
            $table->foreignId('form_id')->constrained();
        });
    }
}
