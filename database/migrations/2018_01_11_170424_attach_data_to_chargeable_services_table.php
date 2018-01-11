<?php

use App\ChargeableService;
use App\Practice;
use Illuminate\Database\Migrations\Migration;

class AttachDataToChargeableServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $service = ChargeableService::updateOrCreate([
            'code'        => 'CPT99490',
            'description' => 'CPT for 99490 is defined as "clinical staff time directed by a physician or other Qualified Health Care Provider (QHCP)".',
            'amount'      => null,
        ]);

        foreach (Practice::active()->get() as $practice) {
            $practice->chargeableServices()->attach($service->id);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
