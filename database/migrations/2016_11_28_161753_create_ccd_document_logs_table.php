<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdDocumentLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ccd_document_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ccda_id')->unsigned()->index('ccd_document_logs_ccda_id_foreign');
            $table->integer('vendor_id')->unsigned()->index('ccd_document_logs_vendor_id_foreign');
            $table->string('type');
            $table->string('custodian');
            $table->boolean('import');
            $table->boolean('invalid');
            $table->boolean('edited');
            $table->softDeletes();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ccd_document_logs');
    }

}
