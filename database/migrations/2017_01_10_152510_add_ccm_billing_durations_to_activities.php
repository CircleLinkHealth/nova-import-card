<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCcmBillingDurationsToActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lv_activities', function (Blueprint $table) {

            $table->integer('post_ccm_duration')->after('duration');
            $table->integer('pre_ccm_duration')->after('duration');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lv_activities', function (Blueprint $table) {

            $table->dropColumn('post_ccm_duration');
            $table->dropColumn('pre_ccm_duration');

        });
    }
}
