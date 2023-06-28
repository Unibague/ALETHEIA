<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->primary('group_id');
            $table->unsignedBigInteger('group_id')->unique()->nullable();
            $table->string('name');
            $table->foreignId('academic_period_id')->constrained();
            $table->string('class_code');
            $table->string('group');
            $table->enum('degree', ['pregrado', 'posgrado','Cursos']);
            $table->string('service_area_code');
            $table->foreign('service_area_code')->references('code')->on('service_areas');
            $table->foreignId('teacher_id')->nullable();
            $table->foreign('teacher_id')->references('id')->on('users');
            $table->enum('hour_type', ['normal', 'cátedra', 'cátedra adicional', 'cátedra administrativa']);
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
        Schema::dropIfExists('groups');
    }
}
