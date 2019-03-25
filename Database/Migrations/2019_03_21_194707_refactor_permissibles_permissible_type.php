<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactorPermissiblesPermissibleType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('permissibles')
           ->select('permissible_type')
           ->groupBy('permissible_type')
           ->pluck('permissible_type')
           ->each(
               function ($type) {
                   \DB::table('permissibles')
                      ->where('permissible_type', $type)
                      ->update(
                          [
                              'permissible_type' => str_replace('App', 'CircleLinkHealth\Customer\Entities', $type),
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
        \DB::table('permissibles')
           ->select('permissible_type')
           ->groupBy('permissible_type')
           ->pluck('permissible_type')
           ->each(
               function ($type) {
                   \DB::table('permissibles')
                      ->where('permissible_type', $type)
                      ->update(
                          [
                              'permissible_type' => str_replace( 'CircleLinkHealth\Customer\Entities', 'App', $type),
                          ]
                      );
               }
           );
    }
}
