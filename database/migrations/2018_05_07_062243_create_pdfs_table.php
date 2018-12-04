<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePdfsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('pdfs');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pdfs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pdfable_type');
            $table->integer('pdfable_id')->unsigned();
            $table->string('filename');
            $table->integer('uploaded_by')->unsigned()->nullable()->index('pdfs_uploaded_by_foreign');
            $table->text('file');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
