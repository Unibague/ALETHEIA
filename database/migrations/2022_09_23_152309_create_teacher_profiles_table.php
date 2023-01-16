<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeacherProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_period_id')->constrained();
            $table->string('identification_number');
            $table->foreignId('user_id')->constrained();

            $table->string('unit_code')->nullable();
            $table->foreign('unit_code')->references('code')->on('units');

            $table->string('position')->nullable();
            $table->enum('teaching_ladder', ['NIN', 'AUX', 'ASI', 'ASO', 'TIT', 'DOCENTE'])->nullable();

            $table->enum('employee_type', ['DTC', 'ADM', 'SEG', 'ESI', 'IDI', 'INT', 'CAT', 'AEL', 'POS', 'AEP'])->nullable();
            $table->enum('status', ['activo', 'inactivo', 'suspendido']);
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
        Schema::dropIfExists('teacher_profiles');
    }
}
