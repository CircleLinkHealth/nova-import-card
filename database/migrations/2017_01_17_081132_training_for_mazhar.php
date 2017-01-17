<?php

use App\Importer\Models\ItemLogs\ProviderLog;
use Illuminate\Database\Migrations\Migration;

class TrainingForMazhar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ProviderLog::where('street', '=', '2698 N GALLOWAY AVE')
            ->orWhere('street', '=', '341 WHEATFIELD')
            ->update([
                'location_id'         => 48,
                'practice_id'         => 21,
                'billing_provider_id' => 852,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
