<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class RenameRevisionables extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        \DB::table('revisions')
            ->where('revisionable_type', 'App\Activity')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\SharedModels\Entities\Activity',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\ActivityMeta')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\SharedModels\Entities\ActivityMeta',
                ]
            );

        \DB::table('revisions')
            ->where('revisionable_type', 'App\PageTimer')
            ->update(
                [
                    'revisionable_type' => 'CircleLinkHealth\SharedModels\Entities\PageTimer',
                ]
            );
    }
}
