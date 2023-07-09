<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnitHierarchyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unit_hierarchy', function (Blueprint $table) {
            $table->id();
            $table->string('father_unit_identifier');
            $table->foreign('father_unit_identifier')->references('identifier')->on('v2_units');
            $table->foreignId('father_unit_owner_id')->references('id')->on('users');
            $table->string('prior_child_unit_identifier')->nullable();
            $table->foreign('prior_child_unit_identifier')->references('identifier')->on('v2_units');
            $table->foreignId('prior_child_unit_owner_id')->nullable()->references('id')->on('users');
            $table->string('child_unit_identifier');
            $table->foreign('child_unit_identifier')->references('identifier')->on('v2_units');
            $table->foreignId('child_unit_owner_id')->references('id')->on('users');
            $table->foreignId('assessment_period_id')->references('id')->on('assessment_periods');
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
        Schema::dropIfExists('unit_hierarchy');
    }
}
