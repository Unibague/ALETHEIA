<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssessmentPeriodIdToResponseIdeals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('response_ideals', function (Blueprint $table) {
            $table->foreignId('assessment_period_id')->constrained();
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
            $table->dropColumn('assessment_period_id');
        });
    }
}
