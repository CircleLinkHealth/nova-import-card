<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameRevisionablesPart2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('revisions')
           ->where('revisionable_type', 'App\Practice')
           ->update(
               [
                   'revisionable_type' => 'CircleLinkHealth\Customer\Entities\Practice',
               ]
           );
    
        \DB::table('revisions')
           ->where('revisionable_type', 'App\PracticeRoleUser')
           ->update(
               [
                   'revisionable_type' => 'CircleLinkHealth\Customer\Entities\PracticeRoleUser',
               ]
           );
    
        \DB::table('revisions')
           ->where('revisionable_type', 'App\ProviderInfo')
           ->update(
               [
                   'revisionable_type' => 'CircleLinkHealth\Customer\Entities\ProviderInfo',
               ]
           );
    
        \DB::table('revisions')
           ->where('revisionable_type', 'App\Settings')
           ->update(
               [
                   'revisionable_type' => 'CircleLinkHealth\Customer\Entities\Settings',
               ]
           );
    
        \DB::table('revisions')
           ->where('revisionable_type', 'App\User')
           ->update(
               [
                   'revisionable_type' => 'CircleLinkHealth\Customer\Entities\User',
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
