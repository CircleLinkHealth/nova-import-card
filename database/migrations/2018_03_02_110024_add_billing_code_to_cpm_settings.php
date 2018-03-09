<?php

use App\ChargeableService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBillingCodeToCpmSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $service = ChargeableService::whereCode('CPT 99490')->first();

        Schema::table('cpm_settings', function (Blueprint $table) use ($service) {
            $table->unsignedInteger('default_chargeable_service_id')
                ->nullable()
                ->after('bill_to')
                ->default($service->id);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cpm_settings', function (Blueprint $table) {
            //
        });
    }
}
