<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeChargeablesTableNamespace extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('chargeables')
           ->select('chargeable_type')
           ->groupBy('chargeable_type')
           ->pluck('chargeable_type')
           ->each(
               function ($type) {
                   \DB::table('chargeables')
                      ->where('chargeable_type', $type)
                      ->update(
                          [
                              'chargeable_type' => str_replace('App', 'CircleLinkHealth\Customer\Entities', $type),
                          ]
                      );
               }
           );
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('chargeables')
           ->select('chargeable_type')
           ->groupBy('chargeable_type')
           ->pluck('chargeable_type')
           ->each(
               function ($type) {
                   \DB::table('chargeables')
                      ->where('chargeable_type', $type)
                      ->update(
                          [
                              'chargeable_type' => str_replace('CircleLinkHealth\Customer\Entities', 'App', $type),
                          ]
                      );
               }
           );
    }
}
