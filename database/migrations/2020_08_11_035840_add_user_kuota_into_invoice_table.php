<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserKuotaIntoInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::table('invoice', function (Blueprint $table) {
           $table->integer('user_kuota')->nullable()->after('status_bayar');
       });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
       Schema::table('invoice', function (Blueprint $table) {
           $table->dropColumn('user_kuota');
       });
     }
}
