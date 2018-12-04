<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPdfsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pdfs', function (Blueprint $table) {
            $table->foreign('uploaded_by')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pdfs', function (Blueprint $table) {
            $table->dropForeign('pdfs_uploaded_by_foreign');
        });
    }
}
