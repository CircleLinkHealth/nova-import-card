<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsForCustomAuditReportSendAlgo extends Migration
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
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedInteger('patient_id')->after('attachment_type')->nullable();

            $table->string('phaxio_event_status')
                ->after('data')
                ->virtualAs('JSON_UNQUOTE(data->"$.status.phaxio.value")')
                ->index();

            $table->string('phaxio_event_type')
                ->after('data')
                ->virtualAs('JSON_UNQUOTE(data->"$.status.phaxio.event_type")')
                ->index();

            $table->string('media_collection_name')
                ->after('patient_id')
                ->virtualAs('JSON_UNQUOTE(data->"$.media_collection_name")')
                ->index();
        });
    }
}
