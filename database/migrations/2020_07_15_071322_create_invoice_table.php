<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('invoice', function (Blueprint $table) {
          $table->increments('id');
          $table->string('order_id')->nullable();
          $table->unique('order_id');
          $table->integer('idperusahaan')->unsigned();
          $table->foreign('idperusahaan')->references('id')->on('perusahaan');
          $table->boolean('status_bayar')->default(0);
          $table->integer('total');
          $table->integer('periode');
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
      Schema::drop('invoice');
    }
}
