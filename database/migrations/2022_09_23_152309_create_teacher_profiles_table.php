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
            $table->string('unity');
            $table->string('position');
            $table->enum('teaching_ladder',['ninguno','auxiliar','asistente','asociado','titular']);
            $table->enum('employee_type',['DTC','ADM','SEG','ESI','IDI','INT','CAT','AEL','POS','AEP']);
            $table->enum('status', ['activo','inactivo','suspendido']);
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
