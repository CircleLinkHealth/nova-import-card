<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Observation;
use App\ObservationMeta;
use App\Services\Observations\ObservationConstants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeverityToObservations extends Migration
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
        Schema::table('lv_observations', function (Blueprint $table) {
            $table->string('severity')->nullable()->after('obs_value');
        });

        DB::transaction(function () {
            ObservationMeta::whereMetaKey('dm_alert_level')->chunkById(1000, function ($metas) {
                foreach ($metas as $meta) {
                    Observation::whereId($meta)->update(['severity' => $meta->meta_value]);
                }
            });

            Observation::whereObsKey('Blood_Pressure')->update(['obs_key' => ObservationConstants::BLOOD_PRESSURE]);
            Observation::whereObsKey('Blood_Sugar')->update(['obs_key' => ObservationConstants::BLOOD_SUGAR]);
            Observation::whereObsKey('Cigarettes')->update(['obs_key' => ObservationConstants::CIGARETTE_COUNT]);
        });
    }
}
