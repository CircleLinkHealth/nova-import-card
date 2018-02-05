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
            'code'        => 'CPT 99490',
        ], [
            'description' => 'CCM Services over 20 mins (1 month).',
            'amount'      => 42.21,
        ]);

        ChargeableService::updateOrCreate([
            'code'        => 'CPT 99487',
        ], [
            'description' => 'Complex CCM over 60 mins (1 month)',
            'amount'      => 92.66,
        ]);

        ChargeableService::updateOrCreate([
            'code'        => 'CPT 99489',
        ], [
            'description' => 'Complex CCM additional 30 mins (1 month)',
            'amount'      => 46.87,
        ]);

        ChargeableService::updateOrCreate([
            'code'        => 'CPT 99484',
        ], [
            'description' => 'Behavioural Health Services over 20 mins (1 month)',
            'amount'      => 48.00,
        ]);

        ChargeableService::updateOrCreate([
            'code'        => 'G0506',
        ], [
            'description' => 'Enrollment in office & Care Planning by Provider',
            'amount'      => 62.00,
        ]);

        ChargeableService::updateOrCreate([
            'code'        => 'G0511',
        ], [
            'description' => 'FQHC / RHC General Care Management (1 month)',
            'amount'      => 61.00,
        ]);

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