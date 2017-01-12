<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCcmBucketsToNurseInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nurse_monthly_summaries', function (Blueprint $table) {

            $table->dropColumn('time');
            $table->dropColumn('ccm_time');
            $table->integer('accrued_towards_ccm')->default(0)->after('month_year');
            $table->integer('accrued_after_ccm')->default(0)->after('month_year');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nurse_monthly_summaries', function (Blueprint $table) {

            $table->integer('time');
            $table->integer('ccm_time');
            $table->dropColumn('accrued_towards_ccm');
            $table->dropColumn('accrued_after_ccm');

        });
    }
}
