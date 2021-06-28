<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmountAndDuedateIntoInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::table('invoice', function (Blueprint $table) {
          $table->integer('amount_received')->default(0)->after('periode');
          $table->date('due_date')->after('amount_received');
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
          $table->dropColumn('amount_received');
          $table->dropColumn('due_date');
       });
     }
}
