<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChargeableServiceIdInOfflineActivityTimeRequestsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('offline_activity_time_requests', 'chargeable_service_id')) {
            Schema::table('offline_activity_time_requests', function (Blueprint $table) {
                $table->dropForeign(['chargeable_service_id']);

                $table->dropColumn('chargeable_service_id');
                $table->boolean('is_behavioral')->nullable(false);
            });
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('offline_activity_time_requests', 'chargeable_service_id')) {
            Schema::table('offline_activity_time_requests', function (Blueprint $table) {
                $table->dropColumn('is_behavioral');
                $table->unsignedInteger('chargeable_service_id')->nullable()->after('is_approved');

                $table->foreign('chargeable_service_id')
                    ->references('id')
                    ->on('chargeable_services')
                    ->onDelete('set null');
            });
        }
    }
}
