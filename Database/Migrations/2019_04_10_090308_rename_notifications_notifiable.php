<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class RenameNotificationsNotifiable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        \DB::table('notifications')
            ->select('notifiable_type')
            ->groupBy('notifiable_type')
            ->pluck('notifiable_type')
            ->each(
                function ($type) {
                    \DB::table('notifications')
                        ->where('notifiable_type', $type)
                        ->update(
                            [
                                'notifiable_type' => str_replace('CircleLinkHealth\Customer\Entities', 'App', $type),
                            ]
                        );
                }
            );
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        \DB::table('notifications')
            ->select('notifiable_type')
            ->groupBy('notifiable_type')
            ->pluck('notifiable_type')
            ->each(
                function ($type) {
                    \DB::table('notifications')
                        ->where('notifiable_type', $type)
                        ->update(
                            [
                                'notifiable_type' => str_replace('App', 'CircleLinkHealth\Customer\Entities', $type),
                            ]
                        );
                }
            );
    }
}
