<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('supplier')->unsigned();
            $table->foreign('supplier')->references('id')->on('supplier');
            $table->bigInteger('product')->unsigned();
            $table->foreign('product')->references('id')->on('product_master_list');

            // $table->text('product_description');

            $table->date('delivery_date');
            $table->string('reference_delivery_document')->nullable();
            $table->string('serial_number')->nullable();
            $table->integer('warranty');
            $table->date('warranty_start');
            $table->date('warranty_end');
            $table->bigInteger('status')->unsigned();
            $table->foreign('status')->references('id')->on('status');
            $table->text('remarks')->nullable();
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('user');
            $table->foreign('updated_by')->references('id')->on('user');
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
        Schema::dropIfExists('product');
    }
}