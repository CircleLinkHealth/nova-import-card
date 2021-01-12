<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditFaxLogsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = 'fax_logs';
        if ( ! Schema::hasColumn($tableName, 'event_type')) {
            Schema::table(
                $tableName,
                function (Blueprint $table) {
                    $table->string('event_type')->nullable()->after('status');
                }
            );

            if (class_exists('CircleLinkHealth\SharedModels\Entities\FaxLog')) {
                \CircleLinkHealth\SharedModels\Entities\FaxLog::orderBy('id')->chunkById(30, function ($faxes) {
                    foreach ($faxes as $fax) {
                        $fax->event_type = $fax->status;
                        $fax->status = $fax->response['status'];
                        $fax->save();
                    }
                });
            }
        }
    }
}
