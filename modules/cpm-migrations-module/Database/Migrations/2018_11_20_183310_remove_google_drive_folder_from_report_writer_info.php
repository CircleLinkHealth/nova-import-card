<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveGoogleDriveFolderFromReportWriterInfo extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('ehr_report_writer_info', function (Blueprint $table) {
            $table->string('google_drive_folder')->nullable();
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        //We only need google_drive_folder_path
        Schema::table('ehr_report_writer_info', function (Blueprint $table) {
            $table->dropColumn('google_drive_folder');
        });
    }
}
