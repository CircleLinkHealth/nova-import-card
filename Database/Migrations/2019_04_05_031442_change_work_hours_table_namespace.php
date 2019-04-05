<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeWorkHoursTableNamespace extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('work_hours')
           ->select('workhourable_type')
           ->groupBy('workhourable_type')
           ->pluck('workhourable_type')
           ->each(
               function ($type) {
                   \DB::table('work_hours')
                      ->where('workhourable_type', $type)
                      ->update(
                          [
                              'workhourable_type' => str_replace('App', 'CircleLinkHealth\Customer\Entities', $type),
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
        \DB::table('work_hours')
           ->select('workhourable_type')
           ->groupBy('workhourable_type')
           ->pluck('workhourable_type')
           ->each(
               function ($type) {
                   \DB::table('work_hours')
                      ->where('workhourable_type', $type)
                      ->update(
                          [
                              'workhourable_type' => str_replace('CircleLinkHealth\Customer\Entities', 'App', $type),
                          ]
                      );
               }
           );
    }
}
