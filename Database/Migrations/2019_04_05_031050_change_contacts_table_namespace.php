<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeContactsTableNamespace extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('contacts')
           ->select('contactable_type')
           ->groupBy('contactable_type')
           ->pluck('contactable_type')
           ->each(
               function ($type) {
                   \DB::table('contacts')
                      ->where('contactable_type', $type)
                      ->update(
                          [
                              'contactable_type' => str_replace('App', 'CircleLinkHealth\Customer\Entities', $type),
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
        \DB::table('contacts')
           ->select('contactable_type')
           ->groupBy('contactable_type')
           ->pluck('contactable_type')
           ->each(
               function ($type) {
                   \DB::table('contacts')
                      ->where('contactable_type', $type)
                      ->update(
                          [
                              'contactable_type' => str_replace('CircleLinkHealth\Customer\Entities', 'App', $type),
                          ]
                      );
               }
           );
    }
}
