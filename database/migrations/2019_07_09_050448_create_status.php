<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
        });


        DB::table('status')->insert([
            [
                'name'      =>      'New'
            ],[
                'name'      =>      'Replaced'
            ],[
                'name'      =>      'Returned'
            ],[
                'name'      =>      'Repaired'
            ]
        ]);
    } 

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('status');
    }
}
