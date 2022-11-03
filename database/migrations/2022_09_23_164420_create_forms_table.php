<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['estudiantes', 'otros']);
            $table->enum('degree', ['pregrado', 'posgrado', 'cursos'])->nullable();
            $table->foreignId('assessment_period_id')->nullable()->constrained();
            $table->json('units_id')->nullable();
            $table->foreignId('academic_period_id')->nullable()->constrained();
            $table->enum('unit_role', ['jefe', 'par', 'autoevaluación'])->nullable();
            $table->enum('teaching_ladder', ['ninguno', 'auxiliar', 'asistente', 'asociado', 'titular'])->nullable();
            $table->json('service_areas_id')->nullable();
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
        Schema::dropIfExists('forms');
    }
}
