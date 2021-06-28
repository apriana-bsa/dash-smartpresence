<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditUnitpriceFromPerusahaanKuotaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::table('perusahaan_kuota', function (Blueprint $table) {
           $table->integer('unitprice')->default(0);
       });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
       Schema::table('perusahaan_kuota', function (Blueprint $table) {
           $table->dropColumn('unitprice');
       });
     }
}
