<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2UnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_units', function (Blueprint $table) {
            $table->primary('identifier');
            $table->string('code');
            $table->string('name');
            $table->foreignId('assessment_period_id')->constrained();
            $table->string('identifier');
            $table->boolean('is_custom');
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
        Schema::dropIfExists('v2_units');
    }
}
