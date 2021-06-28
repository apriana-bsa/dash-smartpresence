<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditStatusBayarFromInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::table('invoice', function (Blueprint $table) {
          $table->integer('status_bayar')->default(0)->change();
          $table->integer('created_by')->unsigned()->nullable()->after('periode');
          $table->foreign('created_by')->references('id')->on('user');
          $table->integer('updated_by')->unsigned()->nullable()->after('created_by');
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
          $table->dropForeign('invoice_created_by_foreign');
          $table->dropColumn('created_by');
          $table->dropColumn('updated_by');
       });
     }
}
