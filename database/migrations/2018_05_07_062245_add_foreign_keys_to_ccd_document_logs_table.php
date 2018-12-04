<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCcdDocumentLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_document_logs', function (Blueprint $table) {
            $table->foreign('billing_provider_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('practice_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccd_document_logs', function (Blueprint $table) {
            $table->dropForeign('ccd_document_logs_billing_provider_id_foreign');
            $table->dropForeign('ccd_document_logs_location_id_foreign');
            $table->dropForeign('ccd_document_logs_practice_id_foreign');
        });
    }
}
