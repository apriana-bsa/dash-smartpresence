<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubscriptionToPerusahaanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::table('perusahaan', function (Blueprint $table) {
           $table->boolean('subscription')->default(0)->after('status');
       });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
       Schema::table('perusahaan', function (Blueprint $table) {
           $table->dropColumn('subscription');
       });
     }
}
