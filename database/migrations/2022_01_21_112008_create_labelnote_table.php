<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabelnoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('labelnote', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('label_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('note_id');
            $table->foreign('label_id')
                ->references('id')
                ->on('labels')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('note_id')
                ->references('id')
                ->on('notes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');    
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
        Schema::dropIfExists('labelnote');
    }
}
