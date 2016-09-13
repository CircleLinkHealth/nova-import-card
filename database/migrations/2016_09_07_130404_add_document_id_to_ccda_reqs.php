<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocumentIdToCcdaReqs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccda_requests', function (Blueprint $table) {
            $table->unsignedInteger('document_id')
                ->nullable()
                ->after('successful_call');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccda_requests', function (Blueprint $table) {
            $table->dropColumn('document_id');
        });
    }
}
