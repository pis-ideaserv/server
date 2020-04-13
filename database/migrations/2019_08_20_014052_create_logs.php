<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('log', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user')->unsigned();
            $table->foreign('user')->references('id')->on('user');
            $table->string('action');  //action = "delete" | "edit" | "update"
            $table->string('target');  //target = "user" | "product" | "supplier"
            $table->text('previous')->nullable();
            $table->text('update');
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
        Schema::dropIfExists('user_logs');
    }
}
                                                                                                                                                                                                