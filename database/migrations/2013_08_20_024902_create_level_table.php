<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLevelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('level', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name');
        });

        DB::table('level')->insert([
            [
                'id'    => 1 ,
                'name'  => 'Admin'
            ],[
                'id'    => 2 ,
                'name'  => 'Encoder'
            ],[
                ' id'   => 3,
                'name'  => 'Viewer'
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
        Schema::dropIfExists('level');
    }
}
