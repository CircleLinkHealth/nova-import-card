<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPdfsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('pdfs', function (Blueprint $table) {
            $table->dropForeign('pdfs_uploaded_by_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pdfs', function (Blueprint $table) {
            $table->foreign('uploaded_by')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }
}
