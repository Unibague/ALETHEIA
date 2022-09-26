<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResponseIdealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('response_ideals', function (Blueprint $table) {
            $table->id();
            $table->enum('teaching_ladder',['ninguno','auxiliar','asistente','asociado','titular']);
            $table->foreignId('form_questions_id')->constrained();
            $table->json('response');
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
        Schema::dropIfExists('response_ideals');
    }
}
