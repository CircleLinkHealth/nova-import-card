<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCcdProviderLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_provider_logs', function (Blueprint $table) {
            $table->foreign('billing_provider_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('practice_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccd_provider_logs', function (Blueprint $table) {
            $table->dropForeign('ccd_provider_logs_billing_provider_id_foreign');
            $table->dropForeign('ccd_provider_logs_location_id_foreign');
            $table->dropForeign('ccd_provider_logs_practice_id_foreign');
            $table->dropForeign('ccd_provider_logs_user_id_foreign');
        });
    }
}
