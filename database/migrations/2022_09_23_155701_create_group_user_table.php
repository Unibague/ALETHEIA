<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_user', function (Blueprint $table) {
            $table->primary(['group_id', 'user_id', 'academic_period_id']);
            $table->unsignedBigInteger('group_id');
            $table->foreign('group_id')->references('group_id')->on('groups');
            $table->foreignId('user_id')->constrained();
            $table->boolean('has_answer');
            $table->foreignId('academic_period_id')->constrained();
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
        Schema::dropIfExists('group_user');
    }
}
