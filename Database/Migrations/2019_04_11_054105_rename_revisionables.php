<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameRevisionables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('revisions')
           ->where('revisionable_type', 'App\Activity')
           ->update(
               [
                   'revisionable_type' => 'CircleLinkHealth\TimeTracking\Entities\Activity',
               ]
           );
    
        \DB::table('revisions')
           ->where('revisionable_type', 'App\ActivityMeta')
           ->update(
               [
                   'revisionable_type' => 'CircleLinkHealth\TimeTracking\Entities\ActivityMeta',
               ]
           );
    
        \DB::table('revisions')
           ->where('revisionable_type', 'App\PageTimer')
           ->update(
               [
                   'revisionable_type' => 'CircleLinkHealth\TimeTracking\Entities\PageTimer',
               ]
           );
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
