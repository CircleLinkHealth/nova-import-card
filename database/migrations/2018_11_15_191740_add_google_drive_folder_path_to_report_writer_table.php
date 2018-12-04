<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoogleDriveFolderPathToReportWriterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ehr_report_writer_info', function (Blueprint $table) {
            $table->string('google_drive_folder_path')->after('google_drive_folder')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ehr_report_writer_info', function (Blueprint $table) {
            $table->dropColumn('google_drive_folder_path');
        });
    }
}
