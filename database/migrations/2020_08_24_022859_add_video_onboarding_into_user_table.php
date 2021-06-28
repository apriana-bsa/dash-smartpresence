<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVideoOnboardingIntoUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::table('user', function (Blueprint $table) {
           $table->boolean('onboardingvideo')->default(1)->after('status');
       });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
       Schema::table('user', function (Blueprint $table) {
           $table->dropColumn('onboardingvideo');
       });
     }
}
